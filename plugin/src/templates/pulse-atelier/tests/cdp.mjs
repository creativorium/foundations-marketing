import { spawn } from 'node:child_process';
import { mkdtemp } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import { join } from 'node:path';

export const delay = ms => new Promise(resolve => setTimeout(resolve, ms));
export async function browser(executable) {
  const profile = await mkdtemp(join(tmpdir(), 'pulse-reference-'));
  const process = spawn(executable, ['--headless=new', '--disable-gpu', '--hide-scrollbars', '--allow-file-access-from-files', '--no-first-run', '--remote-debugging-port=0', `--user-data-dir=${profile}`, 'about:blank'], { windowsHide: true, stdio: ['ignore', 'ignore', 'pipe'] });
  const endpoint = await new Promise((resolve, reject) => {
    const timer = setTimeout(() => reject(new Error('Chrome startup timed out')), 30000);
    process.on('error', reject);
    process.stderr.on('data', data => { const match = data.toString().match(/DevTools listening on (ws:\/\/\S+)/); if (match) { clearTimeout(timer); resolve(match[1]); } });
  });
  const socket = new WebSocket(endpoint);
  await new Promise((resolve, reject) => { socket.onopen = resolve; socket.onerror = reject; });
  let id = 0;
  const pending = new Map();
  socket.onmessage = ({ data }) => {
    const result = JSON.parse(data), promise = pending.get(result.id);
    if (promise) { pending.delete(result.id); clearTimeout(promise.timer); result.error ? promise.reject(new Error(result.error.message)) : promise.resolve(result.result); }
  };
  const send = (method, params = {}, sessionId) => new Promise((resolve, reject) => {
    const next = ++id, timer = setTimeout(() => { pending.delete(next); reject(new Error(method + ' timed out')); }, 30000);
    pending.set(next, { resolve, reject, timer });
    socket.send(JSON.stringify({ id: next, method, params, ...(sessionId ? { sessionId } : {}) }));
  });
  return {
    async page(url, width = 1440, height = 900) {
      const { targetId } = await send('Target.createTarget', { url: 'about:blank' });
      const { sessionId } = await send('Target.attachToTarget', { targetId, flatten: true });
      const call = (method, params) => send(method, params, sessionId);
      const evaluate = async expression => {
        const result = await call('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true });
        if (result.exceptionDetails) throw new Error(JSON.stringify(result.exceptionDetails));
        return result.result.value;
      };
      await call('Page.enable'); await call('Runtime.enable');
      await call('Emulation.setDeviceMetricsOverride', { width, height, deviceScaleFactor: 1, mobile: false });
      await call('Page.navigate', { url });
      return { call, evaluate, targetId, async ready(expression = 'document.readyState === "complete" && document.fonts.status === "loaded"') {
        for (let i = 0; i < 150; i++) { if (await evaluate(expression)) return; await delay(100); }
        throw new Error('Page did not become ready: ' + expression);
      } };
    },
    async close() { try { await send('Browser.close'); } finally { socket.close(); process.kill(); } },
  };
}

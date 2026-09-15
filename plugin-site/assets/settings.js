/* Site settings: accessible page choices and one level of nested links. */
document.querySelectorAll('.fm-link-editor').forEach(editor => {
  const input = editor.querySelector('input');
  let rows, pages;
  try { rows = JSON.parse(input.value); pages = JSON.parse(editor.dataset.pages); } catch { rows = []; pages = []; }
  if (!Array.isArray(rows)) rows = [];
  const social = editor.dataset.field === 'social';
  const sync = () => { input.value = JSON.stringify(rows); };
  const button = (label, action) => {
    const el = document.createElement('button'); el.type='button'; el.className='button'; el.textContent=label;
    el.addEventListener('click',()=>{action();sync();render();}); return el;
  };
  const renderRows = (items, host, depth=0) => items.forEach((row,i) => {
    const line = document.createElement('div'); line.style.margin='12px 0 12px '+(depth?24:0)+'px';
    for (const [key,label] of social ? [['network','Network'],['url','Link URL']] : [['label','Link label'],['url','External URL (optional)']]) {
      const field=document.createElement('input');field.type='text';field.setAttribute('aria-label',label+' '+(i+1));field.placeholder=label;field.value=row[key]||'';
      field.addEventListener('input',()=>{row[key]=field.value;sync();});line.append(field,' ');
    }
    if (!social) {
      const select=document.createElement('select'); select.setAttribute('aria-label','Linked page '+(i+1));
      for(const p of [{id:0,title:'Use external URL'},...pages]) { const o=document.createElement('option');o.value=p.id;o.textContent=p.title;select.append(o); }
      select.value=row.page||0;select.addEventListener('change',()=>{row.page=Number(select.value);sync();});line.append(select,' ');
    }
    line.append(button('Move up',()=>{if(i)[items[i-1],items[i]]=[items[i],items[i-1]];}),' ',button('Remove',()=>items.splice(i,1)));
    if(!social && depth===0) {
      line.append(' ',button('Add child link',()=>{(row.children ||= []).push({label:'',page:0,url:''});}));
      const children=document.createElement('div');renderRows(row.children||[],children,1);line.append(children);
    }
    host.append(line);
  });
  const render=()=>{const host=editor.querySelector('.fm-link-rows');host.replaceChildren();renderRows(rows,host);};
  editor.querySelector('.fm-link-add').addEventListener('click',()=>{rows.push(social?{network:'',url:''}:{label:'',url:'',page:0});sync();render();});
  render();
});
document.querySelectorAll('.fm-pick-image').forEach(button=>button.addEventListener('click',()=>{
  const picker=wp.media({title:'Choose an image',multiple:false,library:{type:'image'}});
  picker.on('select',()=>{document.getElementById(button.dataset.input).value=picker.state().get('selection').first().id;});picker.open();
}));

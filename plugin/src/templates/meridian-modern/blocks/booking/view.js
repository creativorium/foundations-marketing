document.querySelectorAll('[data-meridian-enquiry]').forEach(form => {
  form.addEventListener('submit', event => {
    event.preventDefault();
    if (!form.reportValidity()) return;
    const data = new FormData(form);
    const body = `Name: ${data.get('name')}\nEmail: ${data.get('email')}\n\n${data.get('message')}`;
    const url = `mailto:${encodeURIComponent(form.dataset.email)}?subject=${encodeURIComponent('Free introductory call enquiry')}&body=${encodeURIComponent(body)}`;
    form.querySelector('[role="status"]').textContent = 'Your email app can now open a draft. Review and send it there; this website has not sent your enquiry.';
    window.location.href = url;
  });
});

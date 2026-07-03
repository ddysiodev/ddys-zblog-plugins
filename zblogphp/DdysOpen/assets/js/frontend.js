(function () {
  document.addEventListener('submit', function (event) {
    var form = event.target;
    if (!form || !form.matches || !form.matches('[data-ddys-request-form]')) {
      return;
    }
    if (!window.fetch) {
      return;
    }

    event.preventDefault();
    var status = form.querySelector('.ddys-zbp-request-status');
    var button = form.querySelector('button[type="submit"]');
    if (status) {
      status.textContent = '正在提交...';
    }
    if (button) {
      button.disabled = true;
    }

    fetch(form.action, {
      method: 'POST',
      headers: { Accept: 'application/json' },
      body: new FormData(form),
      credentials: 'same-origin'
    })
      .then(function (response) {
        return response.json().catch(function () {
          return { success: false, message: '服务端返回格式不正确。' };
        });
      })
      .then(function (payload) {
        if (status) {
          status.textContent = payload && payload.message ? payload.message : (payload.success ? '求片已提交。' : '提交失败。');
        }
        if (payload && payload.success) {
          form.reset();
        }
      })
      .catch(function () {
        if (status) {
          status.textContent = '网络错误，请稍后再试。';
        }
      })
      .finally(function () {
        if (button) {
          button.disabled = false;
        }
      });
  });
}());

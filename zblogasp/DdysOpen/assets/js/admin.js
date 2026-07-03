(function () {
  function byId(id) {
    return document.getElementById(id);
  }

  function attr(name, value) {
    return value ? ' ' + name + '="' + String(value).replace(/"/g, '&quot;') + '"' : '';
  }

  document.addEventListener('DOMContentLoaded', function () {
    var build = byId('ddys-asp-shortcode-build');
    var output = byId('ddys-asp-shortcode-output');
    if (build && output) {
      build.addEventListener('click', function () {
        var kind = byId('ddys-asp-shortcode-kind').value;
        var slug = byId('ddys-asp-shortcode-slug').value.trim();
        var limit = byId('ddys-asp-shortcode-limit').value.trim();
        var type = byId('ddys-asp-shortcode-type').value.trim();
        var code = '[' + kind + attr('slug', slug) + attr('limit', limit) + attr('type', type) + ']';
        output.value = code;
        output.focus();
        output.select();
      });
    }

    var copy = byId('ddys-asp-shortcode-copy');
    if (copy && output) {
      copy.addEventListener('click', function () {
        output.focus();
        output.select();
        try {
          document.execCommand('copy');
        } catch (error) {
          /* ignored for older browsers */
        }
      });
    }
  });
})();

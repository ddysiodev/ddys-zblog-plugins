(function () {
  function byId(id) {
    return document.getElementById(id);
  }

  function attr(name, value) {
    if (!value) {
      return '';
    }
    return ' ' + name + '="' + String(value).replace(/"/g, '&quot;') + '"';
  }

  function buildShortcode() {
    var kind = byId('ddys-zbp-shortcode-kind');
    var output = byId('ddys-zbp-shortcode-output');
    if (!kind || !output) {
      return;
    }

    var tag = kind.value || 'ddys_latest';
    var slug = byId('ddys-zbp-shortcode-slug').value.trim();
    var id = byId('ddys-zbp-shortcode-id').value.trim();
    var type = byId('ddys-zbp-shortcode-type').value.trim();
    var limit = byId('ddys-zbp-shortcode-limit').value.trim();
    var perPage = byId('ddys-zbp-shortcode-per-page').value.trim();
    var layout = byId('ddys-zbp-shortcode-layout').value;

    var code = '[' + tag;
    code += attr('slug', slug);
    code += attr('id', id);
    code += attr('type', type);

    if (tag === 'ddys_latest' || tag === 'ddys_hot' || tag === 'ddys_suggest') {
      code += attr('limit', limit);
    }

    if (tag !== 'ddys_movie' && tag !== 'ddys_sources' && tag !== 'ddys_share') {
      code += attr('per_page', perPage);
    }

    code += attr('layout', layout);
    code += ']';
    output.value = code;
  }

  document.addEventListener('click', function (event) {
    if (event.target && event.target.id === 'ddys-zbp-shortcode-build') {
      buildShortcode();
    }

    if (event.target && event.target.id === 'ddys-zbp-shortcode-copy') {
      var output = byId('ddys-zbp-shortcode-output');
      if (output) {
        output.select();
        if (navigator.clipboard) {
          navigator.clipboard.writeText(output.value);
        } else {
          document.execCommand('copy');
        }
      }
    }
  });

  document.addEventListener('change', function (event) {
    if (event.target && event.target.id && event.target.id.indexOf('ddys-zbp-shortcode-') === 0) {
      buildShortcode();
    }
  });
}());

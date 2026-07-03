(function () {
  function escapeHtml(value) {
    return String(value == null ? '' : value).replace(/[&<>"']/g, function (char) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[char];
    });
  }

  function getData(payload) {
    return payload && Object.prototype.hasOwnProperty.call(payload, 'data') ? payload.data : payload;
  }

  function toList(data) {
    if (Array.isArray(data)) return data;
    if (!data || typeof data !== 'object') return [];
    if (Array.isArray(data.items)) return data.items;
    if (Array.isArray(data.movies)) return data.movies;
    if (Array.isArray(data.results)) return data.results;
    if (Array.isArray(data.shares)) return data.shares;
    if (Array.isArray(data.requests)) return data.requests;
    return [data];
  }

  function sourceUrl(item) {
    if (!item) return '';
    if (item.url) return item.url;
    if (item.slug) return 'https://ddys.io/movie/' + encodeURIComponent(item.slug);
    return '';
  }

  function absoluteUrl(url) {
    if (!url) return '';
    if (/^https?:\/\//i.test(url)) return url;
    return 'https://ddys.io' + (url.charAt(0) === '/' ? url : '/' + url);
  }

  function card(item, target) {
    var title = item.title || item.name || item.username || 'Untitled';
    var url = absoluteUrl(sourceUrl(item));
    var poster = item.poster || item.avatar || '';
    var meta = [item.year, item.type || item.type_code, item.rating ? 'Rating ' + item.rating : ''].filter(Boolean).join(' / ');
    return '<article class="ddys-asp-card">' +
      (poster ? '<div class="ddys-asp-poster"><img src="' + escapeHtml(poster) + '" alt="' + escapeHtml(title) + '" loading="lazy"></div>' : '') +
      '<div><h3 class="ddys-asp-title">' + (url ? '<a href="' + escapeHtml(url) + '" target="' + escapeHtml(target) + '" rel="noopener">' + escapeHtml(title) + '</a>' : escapeHtml(title)) + '</h3>' +
      (meta ? '<div class="ddys-asp-meta">' + escapeHtml(meta) + '</div>' : '') +
      (item.description || item.summary || item.note ? '<div class="ddys-asp-meta">' + escapeHtml(item.description || item.summary || item.note).slice(0, 160) + '</div>' : '') +
      '</div></article>';
  }

  function renderList(el, payload) {
    var data = getData(payload);
    var items = toList(data);
    if (!items.length) {
      el.innerHTML = '<div class="ddys-asp-empty">No content found.</div>';
      return;
    }
    var target = el.getAttribute('data-target') || '_blank';
    el.innerHTML = '<div class="ddys-asp-items">' + items.map(function (item) { return card(item, target); }).join('') + '</div>';
  }

  function renderDetail(el, payload) {
    var data = getData(payload);
    if (!data || typeof data !== 'object') {
      renderList(el, payload);
      return;
    }
    var title = data.title || data.name || 'Detail';
    var intro = data.intro || data.description || data.summary || data.note || '';
    el.innerHTML = '<article class="ddys-asp-detail">' + card(data, el.getAttribute('data-target') || '_blank') +
      (intro ? '<div class="ddys-asp-description">' + escapeHtml(intro) + '</div>' : '') +
      '</article>';
  }

  function renderSources(el, payload) {
    var data = getData(payload);
    var groups = data && typeof data === 'object' ? data : {};
    var html = '';
    Object.keys(groups).forEach(function (name) {
      var resources = Array.isArray(groups[name]) ? groups[name] : [];
      if (!resources.length) return;
      html += '<section class="ddys-asp-detail"><h3>' + escapeHtml(name) + '</h3>';
      resources.forEach(function (resource) {
        var title = resource.title || resource.name || resource.download_type || 'Resource';
        var url = resource.url || resource.link || '';
        html += '<p class="ddys-asp-resource">' + (url ? '<a href="' + escapeHtml(url) + '" target="_blank" rel="noopener">' + escapeHtml(title) + '</a>' : escapeHtml(title)) + '</p>';
      });
      html += '</section>';
    });
    el.innerHTML = html || '<div class="ddys-asp-empty">No sources found.</div>';
  }

  function renderCalendar(el, payload) {
    var data = getData(payload);
    var days = data && data.days ? data.days : data;
    if (!days || typeof days !== 'object') {
      renderList(el, payload);
      return;
    }
    var html = '';
    Object.keys(days).forEach(function (day) {
      var items = Array.isArray(days[day]) ? days[day] : [];
      html += '<section class="ddys-asp-calendar-day"><h3>' + escapeHtml(day) + '</h3><div class="ddys-asp-items">' +
        items.map(function (item) { return card(item, el.getAttribute('data-target') || '_blank'); }).join('') +
        '</div></section>';
    });
    el.innerHTML = html || '<div class="ddys-asp-empty">No calendar content found.</div>';
  }

  function buildUrl(el) {
    var params = new URLSearchParams();
    var kind = el.getAttribute('data-kind') || 'latest';
    var route = kind;
    if (kind === 'sources' || kind === 'related' || kind === 'comments') route = kind;
    params.set('route', route);
    ['type', 'genre', 'region', 'year', 'sort', 'page', 'per-page', 'limit', 'q', 'month', 'slug', 'id', 'username'].forEach(function (key) {
      var value = el.getAttribute('data-' + key);
      if (value) params.set(key.replace('-', '_'), value);
    });
    return (el.getAttribute('data-api') || 'api.asp') + '?' + params.toString();
  }

  function loadWidget(el) {
    var kind = el.getAttribute('data-kind') || 'latest';
    if (kind === 'search') {
      renderSearch(el);
      return;
    }
    fetch(buildUrl(el), { credentials: 'same-origin' })
      .then(function (response) { return response.json(); })
      .then(function (payload) {
        if (payload && payload.success === false) throw new Error(payload.message || 'Request failed');
        if (kind === 'movie' || kind === 'collection' || kind === 'share' || kind === 'user') return renderDetail(el, payload);
        if (kind === 'sources') return renderSources(el, payload);
        if (kind === 'calendar') return renderCalendar(el, payload);
        return renderList(el, payload);
      })
      .catch(function (error) {
        el.innerHTML = '<div class="ddys-asp-error">' + escapeHtml(error.message || 'Request failed') + '</div>';
      });
  }

  function renderSearch(el) {
    var q = el.getAttribute('data-q') || '';
    var type = el.getAttribute('data-type') || 'movie';
    el.innerHTML = '<form class="ddys-asp-search-form"><input type="search" name="q" value="' + escapeHtml(q) + '" placeholder="Search DDYS"><select name="type"><option value="movie">movie</option><option value="share">share</option><option value="request">request</option></select><button type="submit">Search</button></form><div class="ddys-asp-search-results"></div>';
    el.querySelector('select').value = type;
    var results = el.querySelector('.ddys-asp-search-results');
    function run() {
      var query = el.querySelector('input').value.trim();
      var selected = el.querySelector('select').value;
      if (!query) {
        results.innerHTML = '';
        return;
      }
      el.setAttribute('data-q', query);
      el.setAttribute('data-type', selected);
      results.innerHTML = '<div class="ddys-asp-loading">Searching...</div>';
      fetch(buildUrl(el), { credentials: 'same-origin' })
        .then(function (response) { return response.json(); })
        .then(function (payload) { renderList(results, payload); })
        .catch(function (error) { results.innerHTML = '<div class="ddys-asp-error">' + escapeHtml(error.message) + '</div>'; });
    }
    el.querySelector('form').addEventListener('submit', function (event) {
      event.preventDefault();
      run();
    });
    if (q) run();
  }

  function bindRequestForm(form) {
    form.addEventListener('submit', function (event) {
      event.preventDefault();
      var status = form.querySelector('.ddys-asp-status');
      status.textContent = 'Submitting...';
      fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        credentials: 'same-origin',
        headers: { Accept: 'application/json' }
      })
        .then(function (response) { return response.json(); })
        .then(function (payload) {
          status.textContent = payload.success === false ? (payload.message || 'Submit failed') : 'Request submitted.';
        })
        .catch(function (error) {
          status.textContent = error.message || 'Submit failed';
        });
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    Array.prototype.forEach.call(document.querySelectorAll('[data-ddys-widget]'), loadWidget);
    Array.prototype.forEach.call(document.querySelectorAll('[data-ddys-request-form]'), bindRequestForm);
  });
})();

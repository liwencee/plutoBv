/* ---- Navigation ---- */
(function () {
  var toggle = document.querySelector('.nav-toggle');
  var nav = document.getElementById('primary-nav');

  if (toggle && nav) {
    toggle.addEventListener('click', function () {
      var expanded = toggle.getAttribute('aria-expanded') === 'true';
      toggle.setAttribute('aria-expanded', String(!expanded));
      nav.classList.toggle('is-open', !expanded);
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && nav.classList.contains('is-open')) {
        nav.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.focus();
      }
    });
  }

  var yearEl = document.getElementById('year');
  if (yearEl) yearEl.textContent = new Date().getFullYear();
})();

/* ---- Print buttons ---- */
document.querySelectorAll('[data-action="print"]').forEach(function (btn) {
  btn.addEventListener('click', function () { window.print(); });
});

/* ---- Scroll reveal ---- */
(function () {
  var targets = document.querySelectorAll('.section');
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  if (!('IntersectionObserver' in window) || reduceMotion) return;

  targets.forEach(function (el) { el.classList.add('reveal'); });

  var observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        entry.target.classList.add('reveal--visible');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12 });

  targets.forEach(function (el) { observer.observe(el); });
})();

/* ---- Form validation + submission ---- */
(function () {
  var forms = document.querySelectorAll('form.form[id]');

  forms.forEach(function (form) {
    var startedInput = form.querySelector('input[name="form_started"]');
    if (startedInput) startedInput.value = String(Date.now());

    form.addEventListener('submit', function (event) {
      event.preventDefault();
      clearErrors(form);
      if (validateForm(form)) submitForm(form);
    });
  });

  function clearErrors(form) {
    form.querySelectorAll('.form-field--error').forEach(function (field) {
      field.classList.remove('form-field--error');
      var msg = field.querySelector('.form-field__error');
      if (msg) msg.remove();
    });
    var status = form.querySelector('.form-status');
    if (status) { status.textContent = ''; status.className = 'form-status'; }
  }

  function showFieldError(field, message) {
    field.classList.add('form-field--error');
    var msg = document.createElement('p');
    msg.className = 'form-field__error';
    msg.textContent = message;
    field.appendChild(msg);
  }

  function validateForm(form) {
    var valid = true;

    form.querySelectorAll('.form-field[data-field]').forEach(function (field) {
      var input = field.querySelector('input, textarea, select');
      if (!input || !input.hasAttribute('required')) return;

      if (input.type === 'file') {
        var file = input.files[0];
        if (!file) {
          showFieldError(field, 'Please choose a file.');
          valid = false;
        } else if (file.size > 5 * 1024 * 1024) {
          showFieldError(field, 'File must be 5MB or smaller.');
          valid = false;
        }
        return;
      }

      if (!input.value.trim()) {
        showFieldError(field, 'This field is required.');
        valid = false;
        return;
      }

      if (input.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value)) {
        showFieldError(field, 'Enter a valid email address.');
        valid = false;
      }
    });

    return valid;
  }

  function submitForm(form) {
    var status = form.querySelector('.form-status');
    var submitBtn = form.querySelector('button[type="submit"]');
    var formData = new FormData(form);

    if (submitBtn) submitBtn.disabled = true;

    fetch(form.action, {
      method: 'POST',
      body: formData,
      headers: { 'Accept': 'application/json' }
    })
      .then(function (response) {
        return response.json().then(function (data) {
          return { ok: response.ok, data: data };
        });
      })
      .then(function (result) {
        if (result.ok && result.data.success) {
          form.reset();
          if (status) {
            status.textContent = result.data.message || "Thanks — we've received your submission.";
            status.className = 'form-status form-success';
          }
        } else if (status) {
          status.textContent = (result.data && result.data.message) || 'Something went wrong. Please try again.';
          status.className = 'form-status form-error';
        }
      })
      .catch(function () {
        if (status) {
          status.textContent = 'Something went wrong sending this. Please try again, or email info@plutobv.co.uk directly.';
          status.className = 'form-status form-error';
        }
      })
      .finally(function () {
        if (submitBtn) submitBtn.disabled = false;
      });
  }
})();

/* ---- Hero slider ---- */
(function () {
  var root = document.querySelector('[data-hero-slider]');
  if (!root) return;

  var slides = Array.prototype.slice.call(root.querySelectorAll('.hero-slider__slide'));
  var dots = Array.prototype.slice.call(root.querySelectorAll('.hero-slider__dot'));
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var current = 0;
  var timer = null;

  function goTo(index) {
    slides[current].classList.remove('is-active');
    dots[current].classList.remove('is-active');

    current = (index + slides.length) % slides.length;

    slides[current].classList.add('is-active');
    dots[current].classList.add('is-active');
  }

  function start() {
    if (reduceMotion || slides.length < 2) return;
    stop();
    timer = window.setInterval(function () { goTo(current + 1); }, 5000);
  }

  function stop() {
    if (timer) { window.clearInterval(timer); timer = null; }
  }

  dots.forEach(function (dot, index) {
    dot.addEventListener('click', function () {
      goTo(index);
      start();
    });
  });

  root.addEventListener('mouseenter', stop);
  root.addEventListener('mouseleave', start);
  root.addEventListener('focusin', stop);
  root.addEventListener('focusout', start);

  start();
})();

/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

(function(window, document) {
  'use strict';

  /* Ottomo
  -------------------------------------------------- */
  /* The admin API: Ottomo.frame(), Ottomo.preview(), Ottomo.editor, Ottomo.sortables, Ottomo.preflight... (see AGENTS.md) */
  var Ottomo = window.Ottomo = Object.assign(window.Ottomo || {}, {
    newsletterModified: false,
    sourceEditor: null,
    snapshot: '',
    adminPath: null,
    sitePath: null,
    editorStyles: [],
    positions: {}
  });

  // Bootstrap Icons, inlined individually as SVGs (mirrors OttomoHelperHTML::icon())
  Ottomo.icons = {
    arrowsMove: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrows-move otm-icon" viewBox="0 0 16 16" aria-hidden="true"><path fill-rule="evenodd" d="M7.646.146a.5.5 0 0 1 .708 0l2 2a.5.5 0 0 1-.708.708L8.5 1.707V5.5a.5.5 0 0 1-1 0V1.707L6.354 2.854a.5.5 0 1 1-.708-.708zM8 10a.5.5 0 0 1 .5.5v3.793l1.146-1.147a.5.5 0 0 1 .708.708l-2 2a.5.5 0 0 1-.708 0l-2-2a.5.5 0 0 1 .708-.708L7.5 14.293V10.5A.5.5 0 0 1 8 10M.146 8.354a.5.5 0 0 1 0-.708l2-2a.5.5 0 1 1 .708.708L1.707 7.5H5.5a.5.5 0 0 1 0 1H1.707l1.147 1.146a.5.5 0 0 1-.708.708zM10 8a.5.5 0 0 1 .5-.5h3.793l-1.147-1.146a.5.5 0 0 1 .708-.708l2 2a.5.5 0 0 1 0 .708l-2 2a.5.5 0 0 1-.708-.708L14.293 8.5H10.5A.5.5 0 0 1 10 8"/></svg>',
    dashCircle: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-dash-circle otm-icon" viewBox="0 0 16 16" aria-hidden="true"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="M4 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 4 8"/></svg>',
    checkCircleFill: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill otm-icon" viewBox="0 0 16 16" aria-hidden="true"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>',
    boxArrowUpRight: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-up-right otm-icon" viewBox="0 0 16 16" aria-hidden="true"><path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5"/><path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0z"/></svg>',
    display: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-display otm-icon" viewBox="0 0 16 16" aria-hidden="true"><path d="M0 4s0-2 2-2h12s2 0 2 2v6s0 2-2 2h-4q0 1 .25 1.5H11a.5.5 0 0 1 0 1H5a.5.5 0 0 1 0-1h.75Q6 13 6 12H2s-2 0-2-2zm1.398-.855a.76.76 0 0 0-.254.302A1.5 1.5 0 0 0 1 4.01V10c0 .325.078.502.145.602q.105.156.302.254a1.5 1.5 0 0 0 .538.143L2.01 11H14c.325 0 .502-.078.602-.145a.76.76 0 0 0 .254-.302 1.5 1.5 0 0 0 .143-.538L15 9.99V4c0-.325-.078-.502-.145-.602a.76.76 0 0 0-.302-.254A1.5 1.5 0 0 0 13.99 3H2c-.325 0-.502.078-.602.145"/></svg>',
    phone: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-phone otm-icon" viewBox="0 0 16 16" aria-hidden="true"><path d="M11 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1zM5 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2z"/><path d="M8 14a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/></svg>',
    sun: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sun otm-icon" viewBox="0 0 16 16" aria-hidden="true"><path d="M8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6m0 1a4 4 0 1 0 0-8 4 4 0 0 0 0 8M8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0m0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13m8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5M3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8m10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0m-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0m9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707M4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708"/></svg>',
    moon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-moon otm-icon" viewBox="0 0 16 16" aria-hidden="true"><path d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278M4.858 1.311A7.27 7.27 0 0 0 1.025 7.71c0 4.02 3.279 7.276 7.319 7.276a7.32 7.32 0 0 0 5.205-2.162q-.506.063-1.029.063c-4.61 0-8.343-3.714-8.343-8.29 0-1.167.242-2.278.681-3.286"/></svg>',
    xCircleFill: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-circle-fill otm-icon" viewBox="0 0 16 16" aria-hidden="true"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293z"/></svg>'
  };



  /* Helpers
  -------------------------------------------------- */
  function $(selector, root) {
    return (root || document).querySelector(selector);
  }

  function $$(selector, root) {
    return Array.prototype.slice.call((root || document).querySelectorAll(selector));
  }

  /** Delegated event listener: handler(event, matchedElement). */
  function on(root, type, selector, handler) {
    root.addEventListener(type, function(event) {
      var target = event.target && event.target.closest ? event.target.closest(selector) : null;
      if (target && root.contains(target)) {
        handler(event, target);
      }
    });
  }

  /** The document of the designer iframe (campaign edit page). */
  function frameDocument() {
    var frame = document.getElementById('otm-designer-frame');
    return frame && frame.contentDocument;
  }

  /**
   * An email's dark mode in a frame: its (prefers-color-scheme: dark) rules on or off, whatever the admin's own scheme
   * (browsers don't pass an iframe's colour scheme to its page). The rules change, not the <style> text, so the
   * designer's HTML keeps them.
   */
  Ottomo.emailScheme = function(doc, dark) {
    if (!doc) {
      return;
    }
    Array.prototype.forEach.call(doc.styleSheets, function(sheet) {
      var rules;
      try {
        rules = sheet.cssRules;
      } catch (e) {
        return; // a stylesheet from another site
      }
      Array.prototype.forEach.call(rules, function(rule) {
        if (!rule.media) {
          return;
        }
        if (rule.otmMedia === undefined) {
          rule.otmMedia = rule.media.mediaText;
        }
        if (/prefers-color-scheme:\s*dark/i.test(rule.otmMedia)) {
          rule.media.mediaText = dark ? rule.otmMedia.replace(/\(\s*prefers-color-scheme:\s*dark\s*\)/i, '(min-width: 0px)') : 'not all';
        }
      });
    });
  };

  /**
   * Every request to the server: fetch with the session cookie and Joomla's AJAX header, the HTTP status checked,
   * and the answer parsed. options: data (a FormData: POST), params (query string), as ('json', the default, or
   * 'text'). Rejects with a readable Error (Joomla answers an invalid token with plain text, not JSON).
   */
  function request(url, options) {
    options = options || {};
    if (options.params) {
      url += (url.indexOf('?') === -1 ? '?' : '&') + new URLSearchParams(options.params).toString();
    }
    return fetch(url, {
      method: options.data ? 'POST' : 'GET',
      body: options.data || undefined,
      credentials: 'same-origin',
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).then(function(response) {
      if (!response.ok) {
        throw new Error(response.status + ' ' + response.statusText);
      }
      return response.text();
    }).then(function(body) {
      if (options.as === 'text') {
        return body;
      }
      try {
        return JSON.parse(body);
      } catch (e) {
        throw new Error(body.replace(/<[^>]+>/g, ' ').trim().slice(0, 200) || 'Invalid response');
      }
    });
  }
  Ottomo.request = request;

  /** A language string passed with Text::script() (Joomla.Text on Joomla 4+, Joomla.JText on Joomla 3). */
  function text(key, fallback) {
    var strings = window.Joomla && (Joomla.Text || Joomla.JText);
    var value = strings ? strings._(key, fallback) : fallback;
    return value || fallback || key;
  }

  function format(key, fallback, n) {
    return text(key, fallback).replace(/%d|%s/, n);
  }

  function onReady(fn) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn);
    } else {
      fn();
    }
  }


  /* Modals: SuperModal frames and the pages inside them
  -------------------------------------------------- */
  (function() {
    function show(root, state) {
      Array.prototype.forEach.call(root.querySelectorAll('[data-state]'), function(el) {
        el.hidden = el.getAttribute('data-state') !== state;
      });
    }

    function progress(root, ratio) {
      var percent = Math.max(0, Math.min(100, Math.floor(ratio * 100)));
      var bar = root.querySelector('.otm-progress');
      if (bar) {
        bar.setAttribute('aria-valuenow', percent);
        bar.querySelector('.otm-progress__bar').style.width = percent + '%';
      }
    }

    /** POST a FormData to the form's action and parse JSON; rejects with a readable message. */
    function postJson(form, data) {
      return request(form.getAttribute('action') || 'index.php', { data: data });
    }

    function fail(root, error) {
      var box = root.querySelector('[data-role="error"]');
      if (box) {
        box.textContent = format('COM_OTTOMO_REQUEST_FAILED', 'The request failed: %s', error && error.message ? error.message : error);
        box.hidden = false;
      }
      show(root, 'failed');
    }

    // ------------------------------------------------------------------ parent side

    var changed = false;

    /** Called by a frame page when it changed data the parent page shows. */
    Ottomo.frameChanged = function() {
      changed = true;
    };

    /** The size of a frame from its URL: sm (a question), md (a task), lg (a picker or a form), xl (a preview) */
    function frameSize(url) {
      if (/view=batch|task=newsletters\.sample|view=export/.test(url)) {
        return 'sm';
      }
      if (/task=newsletters\.popup/.test(url)) {
        return 'md';
      }
      if (/task=newsletters\.preview/.test(url)) {
        return 'xl';
      }
      return 'lg';
    }

    /**
     * Common look of the SuperModal frames: a centred card on a dimmed backdrop (css: dialog.otm-dialog).
     * Below xl, the frame takes the height of its page (same origin), up to the viewport.
     */
    Ottomo.decorateFrame = function(dialog, size) {
      if (!dialog) {
        return;
      }
      dialog.classList.add('otm-dialog', 'otm-dialog--' + size);
      var iframe = dialog.querySelector('iframe');
      // A solid stage behind the frame: the card keeps its width when the frame is narrower (mobile preview)
      if (iframe && !iframe.parentNode.classList.contains('otm-dialog__stage')) {
        var stage = document.createElement('div');
        stage.className = 'otm-dialog__stage';
        iframe.parentNode.insertBefore(stage, iframe);
        stage.appendChild(iframe);
      }
      // One card holds the bar and the stage, with a single border around both
      var bar = dialog.querySelector('.supermodal__bar');
      var stageEl = iframe && iframe.parentNode;
      if (bar && stageEl && !dialog.querySelector('.otm-dialog__card')) {
        var card = document.createElement('div');
        card.className = 'otm-dialog__card';
        bar.parentNode.insertBefore(card, bar);
        card.appendChild(bar);
        card.appendChild(stageEl);
      }
      if (!iframe || size === 'xl') {
        return;
      }
      var observer = null;
      var fit = function() {
        try {
          var doc = iframe.contentDocument;
          var content = doc && (doc.querySelector('.otm-frame') || doc.body);
          if (!content) {
            return;
          }
          var body = doc.defaultView.getComputedStyle(doc.body);
          var height = Math.ceil(content.getBoundingClientRect().bottom + (doc.defaultView.pageYOffset || 0) + (parseFloat(body.paddingBottom) || 0) + (parseFloat(body.marginBottom) || 0));
          dialog.style.setProperty('--otm-dialog-h', height + 'px');
        } catch (e) { /* not same origin: keep the default height */ }
      };
      iframe.addEventListener('load', function() {
        // The frame page (tmpl=component) has no theme attributes of its own: same scheme and accent as the page
        try {
          var root = iframe.contentDocument.documentElement;
          Array.prototype.forEach.call(document.documentElement.attributes, function(attribute) {
            if (/^data-(scheme|navbar|accent|theme)/.test(attribute.name)) {
              root.setAttribute(attribute.name, attribute.value);
            }
          });
        } catch (e) { /* ignore */ }
        fit();
        try {
          var win = iframe.contentWindow;
          var content = iframe.contentDocument.querySelector('.otm-frame') || iframe.contentDocument.body;
          if (observer) {
            observer.disconnect();
          }
          if (win.ResizeObserver) {
            observer = new win.ResizeObserver(fit); // the frame's states (running, finished...) change its height
            observer.observe(content);
          }
        } catch (e) { /* ignore */ }
      });
    };

    /**
     * Open a SuperModal frame, decorated. decorateFrame() moves the iframe into its card, and moving an iframe that is
     * in the page loads it again (a test send went out twice), so the dialog is decorated while SuperModal adds it to
     * the page (document.body.appendChild), before the iframe starts loading.
     */
    Ottomo.openFrame = function(url, title, size) {
      var body = document.body;
      var dialog = null;
      body.appendChild = function(node) {
        if (!dialog && node && node.matches && node.matches('dialog.supermodal--frame')) {
          dialog = node;
          Ottomo.decorateFrame(node, size);
        }
        return Node.prototype.appendChild.call(this, node);
      };
      try {
        SuperModal.frame(url, { title: title || '' });
      } finally {
        delete body.appendChild;
      }
      if (!dialog) {
        // SuperModal added it some other way: decorate it now
        var dialogs = document.querySelectorAll('dialog.supermodal--frame');
        dialog = dialogs[dialogs.length - 1] || null;
        Ottomo.decorateFrame(dialog, size);
      }
      return dialog;
    };

    Ottomo.frame = function(url, title, options) {
      options = options || {};
      if (!window.SuperModal || !SuperModal.frame) {
        window.location.href = url;
        return;
      }
      changed = false;
      var dialog = Ottomo.openFrame(url, title, options.size || frameSize(url));
      if (dialog && options.reload) {
        dialog.addEventListener('close', function() {
          if (changed) {
            window.location.reload();
          }
        });
      }
    };

    // ------------------------------------------------------------------ frame side

    function closeFrame() {
      try {
        if (window.parent !== window && window.parent.SuperModal) {
          window.parent.SuperModal.close();
          return;
        }
      } catch (e) { /* cross-origin: fall through */ }
      window.close();
    }

    function notifyParent() {
      try {
        if (window.parent !== window && window.parent.Ottomo && window.parent.Ottomo.frameChanged) {
          window.parent.Ottomo.frameChanged();
        }
      } catch (e) { /* ignore */ }
    }

    var frames = Ottomo.frames = {};

    /**
     * Send a campaign in batches, after an explicit confirmation click. Each batch goes to the subscribers after
     * the last one ("after", a subscriber id) who haven't received it yet, so "Resume sending" after an error or
     * a closed dialog simply starts again: those already reached are skipped by the server.
     */
    frames.send = function(root) {
      var form = root.querySelector('form');
      var start = root.querySelector('[data-action="start"]');
      var resume = root.querySelector('[data-action="resume"]');
      if (!form || !start) {
        return;
      }
      var total = parseInt(form.elements.total.value, 10) || 0;
      var done = 0;
      var failed = 0;

      function batch() {
        postJson(form, new FormData(form)).then(function(response) {
          if (response.error) {
            throw new Error(response.error);
          }
          var processed = parseInt(response.processed, 10) || 0;
          failed += parseInt(response.failed, 10) || 0;
          done += processed;
          form.elements.after.value = parseInt(response.after, 10) || 0;
          root.querySelector('[data-role="done"]').textContent = Math.min(done, total);
          progress(root, total ? Math.min(done / total, 1) : 1);
          if (processed > 0 && !response.finished) {
            batch();
            return;
          }
          var summary = format('COM_OTTOMO_SENT_TO_N', 'Sent to %d subscribers.', done - failed);
          if (failed) {
            summary += ' ' + format('COM_OTTOMO_SEND_FAILED_N', '%d emails could not be delivered.', failed);
          }
          root.querySelector('[data-role="summary"]').textContent = summary;
          show(root, 'finished');
        }).catch(function(error) {
          fail(root, error);
        });
      }

      start.addEventListener('click', function() {
        start.disabled = true;
        notifyParent();
        show(root, 'running');
        batch();
      });

      if (resume) {
        resume.addEventListener('click', function() {
          form.elements.after.value = 0; // from the start: the server skips those already reached
          show(root, 'running');
          batch();
        });
      }
    };

    /** Export starts on load; the file is then downloaded through an authenticated task. */
    frames.export = function(root) {
      var form = root.querySelector('form');
      if (!form) {
        return;
      }
      var total = parseInt(form.elements.total.value, 10) || 0;
      var done = 0;

      function batch() {
        form.elements.limitstart.value = done;
        postJson(form, new FormData(form)).then(function(response) {
          var counter = parseInt(response.counter, 10) || 0;
          done += counter;
          root.querySelector('[data-role="done"]').textContent = Math.min(done, total);
          progress(root, total ? done / total : 1);
          if (counter > 0 && done < total) {
            batch();
          } else {
            show(root, 'finished');
          }
        }).catch(function(error) {
          fail(root, error);
        });
      }

      batch();
    };

    /** Import from pasted emails, an uploaded CSV file and/or Joomla users. */
    frames.import = function(root) {
      var form = root.querySelector('form');
      var button = root.querySelector('#otm-import-button');
      var csv = form.elements.csv;
      var uploadStatus = root.querySelector('[data-role="upload-status"]');
      var errors = root.querySelector('[data-role="errors"]');
      var label = root.querySelector('[data-role="label"]');

      function log(messages) {
        if (!messages || !messages.length) {
          return;
        }
        messages.forEach(function(message) {
          var line = document.createElement('div');
          line.textContent = message;
          errors.appendChild(line);
        });
        errors.hidden = false;
      }

      // Upload the CSV file as soon as it's chosen; the server keeps its name
      csv.addEventListener('change', function() {
        if (!csv.files.length) {
          return;
        }
        var data = new FormData();
        data.append('csv', csv.files[0]);
        data.append('option', 'com_ottomo');
        data.append('task', 'import.upload');
        data.append('format', 'raw');
        data.append(root.getAttribute('data-token'), '1'); // CSRF token
        button.disabled = true;
        uploadStatus.textContent = text('COM_OTTOMO_CSV_UPLOADING', 'Uploading…');
        postJson(form, data).then(function(response) {
          uploadStatus.textContent = response.error ? response.error : text('COM_OTTOMO_CSV_READY', 'CSV file ready to import.');
          if (response.error) {
            csv.value = '';
          }
        }).catch(function(error) {
          uploadStatus.textContent = error.message;
        }).then(function() {
          button.disabled = false;
        });
      });

      function step() {
        var data = new FormData(form);
        data.delete('csv'); // already uploaded
        postJson(form, data).then(function(response) {
          log(response.errors);
          label.textContent = response.label || '';
          progress(root, parseFloat(response.progress) || 0);
          if (response.status === 'RUNNING') {
            delete response.errors;
            form.elements.request.value = JSON.stringify(response);
            step();
            return;
          }
          form.elements.request.value = '';
          csv.value = '';
          uploadStatus.textContent = '';
          button.disabled = false;
          notifyParent();
        }).catch(function(error) {
          log([error.message]);
          button.disabled = false;
        });
      }

      // Every imported subscriber needs a topic: campaigns are sent to topics
      var topicsError = root.querySelector('[data-role="topics-error"]');
      form.addEventListener('change', function(event) {
        if (event.target.name === 'topics[]' && topicsError) {
          topicsError.hidden = true;
        }
      });

      form.addEventListener('submit', function(event) {
        event.preventDefault();
        if (!form.querySelector('input[name="topics[]"]:checked')) {
          topicsError.textContent = text('COM_OTTOMO_BATCH_SELECT_TOPICS', 'Select at least one topic.');
          topicsError.hidden = false;
          var first = form.querySelector('input[name="topics[]"]');
          if (first) {
            first.focus();
          }
          return;
        }
        errors.textContent = '';
        errors.hidden = true;
        button.disabled = true;
        form.elements.request.value = '';
        show(root, 'running');
        progress(root, 0);
        step();
      });
    };

    /** Batch "Set topics": post, then reload the list behind the frame. */
    frames.batch = function(root) {
      var form = root.querySelector('form');
      if (!form) {
        return;
      }
      var error = root.querySelector('[data-role="error"]');
      form.addEventListener('submit', function(event) {
        event.preventDefault();
        if (!form.querySelector('input[name="topics[]"]:checked')) {
          error.textContent = text('COM_OTTOMO_BATCH_SELECT_TOPICS', 'Select at least one topic.');
          error.hidden = false;
          return;
        }
        form.querySelector('button[type="submit"]').disabled = true;
        request(form.getAttribute('action') || 'index.php', { data: new FormData(form), as: 'text' }).then(function() {
          try {
            window.parent.location.reload();
          } catch (e) {
            closeFrame();
          }
        }).catch(function(err) {
          error.textContent = format('COM_OTTOMO_REQUEST_FAILED', 'The request failed: %s', err.message);
          error.hidden = false;
          form.querySelector('button[type="submit"]').disabled = false;
        });
      });
    };

    // ------------------------------------------------------------------ boot

    function boot() {
      // Links that open a page in a frame: <a href data-frame-open data-frame-title data-frame-reload>
      document.addEventListener('click', function(event) {
        var link = event.target.closest ? event.target.closest('a[data-frame-open]') : null;
        if (link && window.parent === window) {
          event.preventDefault();
          Ottomo.frame(link.getAttribute('href'), link.getAttribute('data-frame-title') || link.textContent.trim(), { reload: link.hasAttribute('data-frame-reload') });
        }
      });
      document.addEventListener('click', function(event) {
        var target = event.target.closest ? event.target.closest('[data-frame-close]') : null;
        if (target) {
          event.preventDefault();
          closeFrame();
        }
      });
      Array.prototype.forEach.call(document.querySelectorAll('.otm-frame[data-frame]'), function(root) {
        var name = root.getAttribute('data-frame');
        if (frames[name]) {
          frames[name](root);
        }
      });
    }

    onReady(boot);
  })();


  /* Campaign preview (a frame with a Desktop / Mobile toggle)
  -------------------------------------------------- */
  (function() {
    /**
     * Campaign preview in a SuperModal frame, with a Desktop / Mobile toggle in the frame's toolbar
     * (the mobile view narrows the frame to a phone's width: the email's own responsive CSS applies).
     * "Open in new tab" opens publicUrl (a sent campaign's page on the site), else the preview itself (drafts are not public).
     */
    Ottomo.preview = function(url, title, publicUrl) {
      if (!window.SuperModal) {
        return;
      }
      var dialog = Ottomo.openFrame(url, title, 'xl');
      var tools = dialog && dialog.querySelector('.supermodal__tools');
      if (!tools) {
        return;
      }
      dialog.classList.add('otm-preview-frame');
      var t = text;
      // Toggle groups: the width (desktop / mobile) and the colour scheme the email is shown in (its dark mode)
      var toggles = function(label, choices, onChoose) {
        var group = document.createElement('div');
        group.className = 'otm-preview-devices';
        group.setAttribute('role', 'group');
        group.setAttribute('aria-label', label);
        choices.forEach(function(choice, index) {
          var button = document.createElement('button');
          button.type = 'button';
          button.className = 'supermodal__btn';
          button.setAttribute('data-device', choice[0]);
          button.setAttribute('aria-pressed', index === 0 ? 'true' : 'false');
          button.title = choice[2];
          button.setAttribute('aria-label', choice[2]);
          button.innerHTML = choice[1];
          button.addEventListener('click', function() {
            onChoose(choice[0]);
            Array.prototype.forEach.call(group.querySelectorAll('button'), function(other) {
              other.setAttribute('aria-pressed', other === button ? 'true' : 'false');
            });
          });
          group.appendChild(button);
        });
        return group;
      };
      var group = toggles(t('COM_OTTOMO_EDITOR_DEVICE', 'Preview size'), [['desktop', Ottomo.icons.display, t('COM_OTTOMO_EDITOR_DESKTOP', 'Desktop')], ['mobile', Ottomo.icons.phone, t('COM_OTTOMO_EDITOR_MOBILE', 'Mobile')]], function(device) {
        dialog.classList.toggle('is-mobile', device === 'mobile');
      });
      var applyScheme = function() {
        var frame = dialog.querySelector('iframe');
        try {
          Ottomo.emailScheme(frame && frame.contentDocument, dialog.classList.contains('is-dark'));
        } catch (e) {
          // not loaded yet
        }
      };
      var schemes = toggles(t('COM_OTTOMO_EDITOR_SCHEME', 'Colour scheme'), [['light', Ottomo.icons.sun, t('COM_OTTOMO_EDITOR_LIGHT', 'Light')], ['dark', Ottomo.icons.moon, t('COM_OTTOMO_EDITOR_DARK', 'Dark')]], function(scheme) {
        dialog.classList.toggle('is-dark', scheme === 'dark');
        applyScheme();
      });
      var previewFrame = dialog.querySelector('iframe');
      if (previewFrame) {
        previewFrame.addEventListener('load', applyScheme);
        applyScheme();
      }
      tools.insertBefore(schemes, tools.firstChild);
      tools.insertBefore(group, tools.firstChild);
      // The email on its own, as readers see it with "View in browser"
      var open = document.createElement('a');
      open.className = 'supermodal__btn otm-preview-open';
      open.href = publicUrl || url;
      open.target = '_blank';
      open.rel = 'noopener';
      open.innerHTML = Ottomo.icons.boxArrowUpRight;
      var label = document.createElement('span');
      label.textContent = t('COM_OTTOMO_OPEN_IN_NEW_TAB', 'Open in new tab');
      open.appendChild(label);
      tools.insertBefore(open, schemes.nextSibling);
    };
  })();


  /* Admin pages: lists, filters, campaign wizard, designer glue, settings
  -------------------------------------------------- */
  (function() {
    function setText(selector, value) {
      $$(selector).forEach(function(el) {
        el.textContent = value;
      });
    }

    /** Decode HTML entities without parsing or running anything (a <textarea> holds raw text). */
    function decodeEntities(value) {
      var textarea = document.createElement('textarea');
      textarea.innerHTML = value || '';
      return textarea.value;
    }

    var reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // ------------------------------------------------------------------ designer glue

    // Markup for a sortable item inside a designer position (same markup as OttomoHelperTemplate, edit mode)
    Ottomo.sortableItem = function(data) {
      var t = function(key, fallback) {
        return text(key, fallback).replace(/"/g, '&quot;');
      };
      return '<div class="text-right otm-sortable-item"><div class="text-left otm-position-content">' + data + '</div>' +
        '<button type="button" class="otm-nsl-btn otm-nsl-btn--move otm-sortable-handle" title="' + t('COM_OTTOMO_EDITOR_MOVE', 'Move') + '" aria-label="' + t('COM_OTTOMO_EDITOR_MOVE', 'Move') + '">' + Ottomo.icons.arrowsMove + '</button>' +
        '<button type="button" class="otm-nsl-btn otm-nsl-btn--remove otm-sortable-remove-button" title="' + t('COM_OTTOMO_EDITOR_REMOVE_BLOCK', 'Remove') + '" aria-label="' + t('COM_OTTOMO_EDITOR_REMOVE_BLOCK', 'Remove') + '">' + Ottomo.icons.dashCircle + '</button></div>';
    };

    /** Append a block to a designer position (by position id) and refresh the HTML tab. */
    function appendBlock(positionId, html) {
      var doc = frameDocument();
      var position = doc && doc.getElementById(positionId);
      if (!position) {
        return null;
      }
      position.insertAdjacentHTML('beforeend', Ottomo.sortableItem(html));
      Ottomo.newsletterModified = true;
      Ottomo.updateSourceEditor();
      if (Ottomo.editor) {
        Ottomo.editor.fitImages(position.lastElementChild);
      }
      return position.lastElementChild;
    }

    // Designer document -> HTML tab: positions become <div data-position> with their items, editor UI removed
    Ottomo.updateSourceEditor = function() {
      var doc = frameDocument();
      if (!doc || !Ottomo.sourceEditor) {
        return;
      }
      var clone = doc.documentElement.cloneNode(true);
      $$('.otm-image-outline', clone).forEach(function(outline) {
        outline.parentNode.removeChild(outline);
      });
      Ottomo.editorStyles = $$('link[rel="stylesheet"]', clone).map(function(link) {
        var html = link.outerHTML;
        link.parentNode.removeChild(link);
        return html;
      });
      $$('.otm-position', clone).forEach(function(position) {
        var data = $('.otm-extensions-data', position);
        var name = data ? data.id : '';
        Ottomo.positions[name] = position;
        var wrapper = clone.ownerDocument.createElement('div');
        wrapper.setAttribute('data-position', name);
        wrapper.innerHTML = $$('.otm-position-content', position).map(function(item) {
          return '<div data-role="position-item">' + item.innerHTML + '</div>';
        }).join('');
        position.parentNode.insertBefore(wrapper, position);
        position.parentNode.removeChild(position);
      });
      Ottomo.sourceEditor.setValue(clone.outerHTML, -1);
      if (Ottomo.snapshot) {
        Ottomo.snapshot = Ottomo.sourceEditor.getValue();
      }
    };

    // HTML tab -> designer document: positions are rebuilt with their editor UI
    Ottomo.updateIframeSource = function() {
      var doc = frameDocument();
      var value = Ottomo.sourceEditor.getValue().replace('</head>', (Ottomo.editorStyles || []).join('') + '</head>');
      doc.documentElement.innerHTML = value;
      $$('[data-position]', doc).forEach(function(position) {
        var editorPosition = Ottomo.positions[position.getAttribute('data-position')];
        if (editorPosition) {
          var contents = $$('.otm-position-content', editorPosition);
          $$('[data-role="position-item"]', position).forEach(function(item, index) {
            if (contents[index]) {
              contents[index].innerHTML = item.innerHTML;
            }
          });
          var rebuilt = doc.createElement('div');
          rebuilt.className = 'otm-position';
          rebuilt.innerHTML = editorPosition.innerHTML;
          position.parentNode.insertBefore(rebuilt, position);
        }
        position.parentNode.removeChild(position);
      });
      Ottomo.designerAttach();
    };

    // Recipients counter and summary of the selected topics
    Ottomo.updateRecipients = function() {
      var summary = $$('[data-role="summary-recipients"]');
      var params = new URLSearchParams({ option: 'com_ottomo', task: 'topics.countUniqueSubscribers', format: 'raw' });
      summary.forEach(function(list) {
        list.textContent = '';
      });
      $$('input[name="topics[]"]:checked').forEach(function(input) {
        params.append('topics[]', input.value);
        summary.forEach(function(list) {
          var item = document.createElement('li');
          item.textContent = (input.getAttribute('data-name') || '') + ' (' + (parseInt(input.getAttribute('data-subscribers'), 10) || 0) + ')';
          list.appendChild(item);
        });
      });
      return request('index.php', { params: params, as: 'text' }).then(function(count) {
        setText('#otm-total-recipients, [data-role="summary-recipients-counter"]', count.trim());
        document.dispatchEvent(new CustomEvent('ottomo:recipients', { detail: { count: parseInt(count, 10) || 0 } }));
      });
    };

    // Content of an extension item (article, K2 item, banner...) inserted from the extension frame
    Ottomo.getExtensionData = function(type, extension, id, editor, template) {
      var params = new URLSearchParams({ option: 'com_ottomo', task: 'extension.getData', extension: extension, id: id, type: type, template: template, format: 'raw' });
      request('index.php', { params: params, as: 'text' }).then(function(html) {
        appendBlock(editor, html);
        if (window.SuperModal) {
          SuperModal.close();
        }
      });
      return false;
    };

    // Newsletter edit view
    Ottomo.newsletter = function() {
      var frame = document.getElementById('otm-designer-frame');
      var form = document.getElementById('adminForm');

      // Recipients
      Ottomo.updateRecipients();
      $$('input[name="topics[]"]').forEach(function(input) {
        input.addEventListener('change', Ottomo.updateRecipients);
      });

      // HTML tab (Ace): edits rebuild the designer and turn the newsletter into a snapshot
      if (window.ace && document.getElementById('otm-source-editor')) {
        Ottomo.sourceEditor = ace.edit('otm-source-editor');
        Ottomo.sourceEditor.setTheme('ace/theme/monokai');
        Ottomo.sourceEditor.getSession().setMode('ace/mode/html');
        Ottomo.sourceEditor.setOptions({ fontSize: '15px', showPrintMargin: false, wrap: true }); // wrap long lines: no horizontal scrolling
        var timeout;
        Ottomo.sourceEditor.on('change', function() {
          window.clearTimeout(timeout);
          if (Ottomo.sourceEditor.isFocused()) {
            timeout = window.setTimeout(function() {
              Ottomo.updateIframeSource();
              Ottomo.snapshot = Ottomo.sourceEditor.getValue();
            }, 1000);
          }
        });
      }

      // Designer iframe: set up on every (re)load (template change); also if it loaded before this ran
      var onFrameLoad = function() {
        Ottomo.designer();
        Ottomo.updateSourceEditor();
      };
      if (frame) {
        frame.addEventListener('load', onFrameLoad);
        var doc = frame.contentDocument;
        if (doc && doc.readyState === 'complete' && doc.URL !== 'about:blank') {
          onFrameLoad();
        }
      }

      // Template choice
      var active = $('[data-role="template"].active');
      $$('[data-role="summary-template"]').forEach(function(summary) {
        summary.innerHTML = active ? active.innerHTML : '';
      });
      var chooseTemplate = function(event, el) {
        if (event.target.closest('.template-preview-image') || el.closest('[data-role="summary-template"]')) {
          return; // the preview button opens the preview; the summary copy is not a choice
        }
        $$('[data-role="template"]').forEach(function(item) {
          item.classList.remove('active', 'otm-grid-item--selected');
          item.setAttribute('aria-pressed', 'false');
        });
        el.classList.add('active', 'otm-grid-item--selected');
        el.setAttribute('aria-pressed', 'true');
        var newValue = el.getAttribute('data-template');
        if (form.elements.template.value !== newValue) {
          form.elements.template.value = newValue;
          $$('[data-role="summary-template"]').forEach(function(summary) {
            summary.innerHTML = el.innerHTML;
          });
          Ottomo.updateData();
          form.elements.snapshot.value = '';
          frame.src = 'index.php?option=com_ottomo&view=designer&format=raw&id=' + encodeURIComponent(form.elements.id.value) + '&template=' + encodeURIComponent(newValue);
        }
      };
      on(document, 'click', '[data-role="template"]', chooseTemplate);
      on(document, 'keydown', '[data-role="template"]', function(event, el) {
        if (event.key === 'Enter' || event.key === ' ') {
          event.preventDefault();
          chooseTemplate(event, el);
        }
      });

      // Summary mirrors the campaign details
      var mirror = function(name, target) {
        var input = form.elements[name];
        if (!input) {
          return;
        }
        // An empty optional field (the sender) shows its default: the placeholder
        var show = function() {
          setText(target, input.value || input.getAttribute('placeholder') || '');
        };
        show();
        ['input', 'change'].forEach(function(type) {
          input.addEventListener(type, show);
        });
      };
      mirror('subject', '[data-role="summary-subject"]');
      mirror('senderName', '[data-role="summary-sender-name"]');
      mirror('senderEmail', '[data-role="summary-sender-email"]');

      // Track changes (the test-send address is not part of the newsletter)
      on(document, 'change', 'input, textarea', function(event, el) {
        if (el.name !== 'emails' && form.contains(el)) {
          Ottomo.newsletterModified = true;
        }
      });
    };

    // Designer iframe document (on each load)
    Ottomo.designer = function() {
      var doc = frameDocument();
      if (!doc) {
        return;
      }
      Ottomo.snapshot = decodeEntities(document.getElementById('adminForm').elements.snapshot.value);
      if (Ottomo.snapshot) {
        doc.documentElement.innerHTML = Ottomo.snapshot;
      }

      // Editor + drag-and-drop (Designer section below)
      Ottomo.designerAttach();

      if (doc.ottomoDesignerBound) {
        return;
      }
      doc.ottomoDesignerBound = true;

      // Extension pickers (SuperModal frames)
      on(doc, 'click', '.otm-modal', function(event, link) {
        event.preventDefault();
        if (window.SuperModal) {
          Ottomo.frame(link.getAttribute('href'), link.getAttribute('title') || link.textContent.trim(), { size: 'lg' });
        }
      });

      // Remove a block
      on(doc, 'click', '.otm-sortable-remove-button', function(event, button) {
        event.preventDefault();
        var item = button.closest('.otm-sortable-item');
        if (item) {
          item.parentNode.removeChild(item);
        }
        Ottomo.newsletterModified = true;
        Ottomo.updateSourceEditor();
      });

      // Add a custom HTML block, ready to type into
      on(doc, 'click', '.otm-custom-content-button', function(event, button) {
        event.preventDefault();
        var item = appendBlock(button.getAttribute('data-target'), '<div contenteditable="true"></div>');
        var editable = item && $('[contenteditable]', item);
        if (editable) {
          editable.focus();
        }
      });
    };

    // Extension frame: "Insert content" / "Insert link" hand the item over to the designer
    Ottomo.extension = function() {
      on(document, 'click', '.otm-insert-content-button, .otm-insert-link-button', function(event, button) {
        event.preventDefault();
        var value = function(name) {
          var input = $('input[name="' + name + '"]');
          return input ? input.value : '';
        };
        parent.Ottomo.getExtensionData(button.classList.contains('otm-insert-content-button') ? 'content' : 'link',
          value('extension'), button.getAttribute('href'), value('editor'), value('template'));
      });
    };

    // Update the newsletter form before saving.
    // Fields are looked up by name inside the form: a position id such as "content" or "header" can also be
    // the id of an element of the admin template, so document-wide id lookups silently lost those positions.
    Ottomo.updateData = function() {
      var form = document.querySelector('#otm-newsletter-form form') || document.getElementById('adminForm');
      var doc = frameDocument();
      var field = function(position) {
        var name = 'ottomoPosition_' + position;
        var input = form.querySelector('input[type="hidden"][name="' + name + '"]');
        if (!input) {
          input = document.createElement('input');
          input.type = 'hidden';
          input.name = name;
          input.className = 'otm-position';
          form.appendChild(input);
        }
        return input;
      };
      $$('.otm-text-data', doc).forEach(function(el) {
        field(el.id).value = el.textContent;
      });
      $$('.otm-extensions-data', doc).forEach(function(el) {
        field(el.id).value = $$('.otm-position-content', el).map(function(item) {
          return item.innerHTML;
        }).join('{extension}');
      });
      form.querySelector('input[name="snapshot"]').value = Ottomo.snapshot;
    };

    // Wizard steps (newsletter edit)
    Ottomo.tabs = function() {
      var links = $$('[data-role="tabs-navigation"] a');
      var panes = $$('[data-role="tabs-content"]');
      if (!links.length) {
        return;
      }
      var show = function(index) {
        if (index < 0 || index >= links.length) {
          return;
        }
        links.forEach(function(link, i) {
          link.classList.toggle('active', i === index);
        });
        // Explicit display: the panes are Bootstrap .tab-pane elements, hidden by CSS unless .active
        panes.forEach(function(pane, i) {
          pane.style.display = i === index ? 'block' : 'none';
          pane.classList.toggle('active', i === index);
        });
      };
      var current = function() {
        return links.indexOf($('[data-role="tabs-navigation"] a.active'));
      };
      links.forEach(function(link, index) {
        link.addEventListener('click', function(event) {
          event.preventDefault();
          show(index);
        });
      });
      on(document, 'click', '[data-role="tabs-next"]', function(event) {
        event.preventDefault();
        show(current() + 1);
      });
      on(document, 'click', '[data-role="tabs-previous"]', function(event) {
        event.preventDefault();
        show(current() - 1);
      });
      show(0);
    };

    // ------------------------------------------------------------------ page setup

    function submitWith(name, value) {
      var form = document.getElementById('adminForm');
      var input = document.createElement('input');
      input.type = 'hidden';
      input.name = name;
      input.value = value; // a property, not markup: quotes and tags stay text
      form.appendChild(input);
      form.submit();
    }

    function setupLists(task) {
      // Filters and search
      on(document, 'change', '#otm-admin-filters select', function(event, select) {
        submitWith(select.name, select.value);
      });
      var search = document.getElementById('search');
      if (search) {
        on(document, 'click', '#otm-search-button', function(event) {
          event.preventDefault();
          submitWith('search', search.value);
        });
        search.addEventListener('keydown', function(event) {
          if (event.key === 'Enter') {
            event.preventDefault();
            submitWith('search', search.value);
          }
        });
      }

      // Selection toolbar: shown while rows are checked (Joomla.checkAll runs first, from the onclick attribute)
      var toolbar = document.getElementById('otm-toolbar');
      if (toolbar && task === '') {
        var refresh = function() {
          var checked = $$('input[name="cid[]"]:checked');
          toolbar.style.display = checked.length ? '' : 'none';
          $$('input[name="cid[]"]').forEach(function(box) {
            var item = box.closest('.otm-grid-item');
            if (item) {
              item.classList.toggle('otm-grid-item--selected', box.checked);
            }
          });
        };
        refresh();
        on(document, 'click', 'input[name="checkall-toggle"], input[name="cid[]"]', function() {
          window.setTimeout(refresh, 0);
        });
      }

      // Publish/unpublish buttons: Joomla's icon font glyphs -> inline Bootstrap Icons SVGs
      // (Joomla 3: .btn.btn-micro buttons; Joomla 4+: .tbody-icon links)
      $$('.otm-listed-items .btn.btn-micro, .otm-listed-items .tbody-icon').forEach(function(button) {
        button.classList.remove('btn', 'btn-micro', 'tbody-icon', 'active');
        button.classList.add('otm-state-toggler');
        // Joomla 4+: the link's task (data-item-id/-task) is run by Joomla's multiselect script, which Ottomo doesn't load
        if (button.hasAttribute('data-item-task')) {
          button.addEventListener('click', function(event) {
            event.preventDefault();
            Joomla.listItemTask(button.getAttribute('data-item-id'), button.getAttribute('data-item-task'));
          });
        }
        $$('.icon-unpublish', button).forEach(function(icon) {
          icon.outerHTML = Ottomo.icons.xCircleFill.replace('otm-icon', 'otm-icon otm-unpublished');
        });
        $$('.icon-publish', button).forEach(function(icon) {
          icon.outerHTML = Ottomo.icons.checkCircleFill.replace('otm-icon', 'otm-icon otm-published');
        });
      });
    }

    function setupEditorTabs() {
      // Visual / HTML / Plain text tabs of the designer
      $$('#otm-editor-tabs a').forEach(function(link) {
        link.addEventListener('click', function(event) {
          event.preventDefault();
          var target = $(link.getAttribute('href'));
          if (!target) {
            return;
          }
          $$('#otm-editor-tabs li').forEach(function(li) {
            li.classList.toggle('active', li === link.parentNode);
          });
          $$('.tab-pane', target.parentNode).forEach(function(pane) {
            pane.classList.toggle('active', pane === target);
          });
          if (Ottomo.sourceEditor && target.contains(Ottomo.sourceEditor.container)) {
            Ottomo.sourceEditor.resize();
          }
        });
      });
    }

    function setupTopicsSelection(view) {
      // "Check all" / "Reset" for the topic checkboxes (newsletter and subscriber forms)
      var all = document.getElementById('otm-addall');
      var boxes = function() {
        return $$('.otm-topics input[type="checkbox"], input[name="topics[]"]').filter(function(box) {
          return box !== all;
        });
      };
      var changed = function() {
        if (view === 'newsletters') {
          Ottomo.updateRecipients();
        }
      };
      if (all) {
        all.addEventListener('change', function() {
          boxes().forEach(function(box) {
            box.checked = all.checked;
          });
          changed();
        });
      }
      on(document, 'click', '#otm-reset', function(event) {
        event.preventDefault();
        boxes().forEach(function(box) {
          box.checked = false;
        });
        if (all) {
          all.checked = false;
        }
        changed();
      });
    }

    function setupMessages() {
      // Close buttons (Bootstrap's alert plugin is not loaded)
      on(document, 'click', '#system-message-container .close, #system-message-container [data-dismiss="alert"]', function(event, button) {
        event.preventDefault();
        var alert = button.closest('.alert');
        if (alert) {
          alert.parentNode.removeChild(alert);
        }
      });

      // Success/info messages fade out after 3 seconds; errors and warnings stay until dismissed
      window.setTimeout(function() {
        var container = document.getElementById('system-message-container');
        if (!container) {
          return;
        }
        var alerts = $$('.alert, joomla-alert', container).filter(function(alert) {
          return !alert.matches('.alert-error, .alert-danger, .alert-warning, joomla-alert[type="danger"], joomla-alert[type="error"], joomla-alert[type="warning"]') && !alert.closest('noscript');
        });
        alerts.forEach(function(alert) {
          var hide = function() {
            alert.hidden = true;
            alert.style.display = 'none';
          };
          if (reducedMotion) {
            hide();
            return;
          }
          alert.style.transition = 'opacity 0.4s ease';
          alert.style.opacity = '0';
          window.setTimeout(hide, 400);
        });
      }, 3000);
    }

    function setupModals() {
      // Newsletter preview (SuperModal frame)
      on(document, 'click', '[data-role="preview-button"]', function(event, button) {
        event.preventDefault();
        var template = $('input[name="template"]');
        var row = button.closest('tr');
        var rowLink = row ? $('td a', row) : null;
        var href = button.getAttribute('href') || ('index.php?option=com_ottomo&task=newsletters.preview&id=' + encodeURIComponent(button.getAttribute('data-id')) + '&template=' + encodeURIComponent(template ? template.value : ''));
        if (window.SuperModal) {
          Ottomo.preview(href, (rowLink ? rowLink.textContent.trim() : '') || button.getAttribute('title') || 'Preview', button.getAttribute('data-public-url'));
        }
      });

      // Template preview image (SuperModal lightbox)
      on(document, 'click', '.template-preview-image', function(event, link) {
        event.preventDefault();
        var item = link.closest('.otm-grid-item');
        var heading = item ? $('h3', item) : null;
        if (window.SuperModal && link.getAttribute('href')) {
          SuperModal.open([{ src: link.getAttribute('href'), title: (heading ? heading.textContent.trim() : '') || link.getAttribute('title') || '' }], 0);
        }
      });
    }

    function setupNavigation() {
      // Tablet / mobile: the menu button shows and hides the main menu
      // The folded sidebar (Atum, narrow screens): the menu button slides it in; the close button, a click outside it,
      // Escape and a chosen page close it
      var sidebarToggle = function(open) {
        var toggle = document.getElementById('otm-navtoggle');
        var sidebar = document.getElementById('otm-sidebar');
        if (!toggle || !sidebar) {
          return;
        }
        open = open === undefined ? !sidebar.classList.contains('is-open') : open;
        sidebar.classList.toggle('is-open', open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) {
          var first = sidebar.querySelector('a, button');
          if (first) {
            first.focus();
          }
        }
      };
      on(document, 'click', '#otm-navtoggle', function(event) {
        event.preventDefault();
        sidebarToggle();
      });
      on(document, 'click', '[data-otm-navclose]', function(event) {
        event.preventDefault();
        sidebarToggle(false);
        document.getElementById('otm-navtoggle').focus();
      });
      document.addEventListener('click', function(event) {
        var sidebar = document.getElementById('otm-sidebar');
        if (sidebar && sidebar.classList.contains('is-open') && !sidebar.contains(event.target) && !event.target.closest('#otm-navtoggle')) {
          sidebarToggle(false);
        }
      });
      document.addEventListener('keydown', function(event) {
        var sidebar = document.getElementById('otm-sidebar');
        if (event.key === 'Escape' && sidebar && sidebar.classList.contains('is-open')) {
          sidebarToggle(false);
          document.getElementById('otm-navtoggle').focus();
        }
      });
    }

    /**
     * Toolbar actions of the simple edit forms, declared on #adminForm instead of an inline script each:
     * data-otm-required (a field name) + data-otm-required-message: the field must be filled (Cancel excepted);
     * data-otm-confirm-task + data-otm-confirm-message: that task asks first;
     * data-otm-source (a field name): Save puts the code editor's text (Ace) in that field.
     */
    function setupFormRules() {
      var form = document.getElementById('adminForm');
      if (!form || !window.Joomla || !['data-otm-required', 'data-otm-confirm-task', 'data-otm-source'].some(function(name) { return form.hasAttribute(name); })) {
        return;
      }
      Joomla.submitbutton = function(task) {
        if (!/\.cancel$/.test(task)) {
          var required = form.getAttribute('data-otm-required');
          var field = required && form.elements[required];
          if (field && String(field.value).trim() === '') {
            window.alert(form.getAttribute('data-otm-required-message'));
            field.focus();
            return false;
          }
          if (task === form.getAttribute('data-otm-confirm-task') && !window.confirm(form.getAttribute('data-otm-confirm-message'))) {
            return false;
          }
          var source = form.getAttribute('data-otm-source');
          if (source && Ottomo.sourceEditor && form.elements[source]) {
            form.elements[source].value = Ottomo.sourceEditor.getValue();
          }
        }
        Joomla.submitform(task);
      };
    }

    // Settings > Sending > Test connection (tests the saved settings)
    function setupConnectionTest() {
      on(document, 'click', '[data-action="test-connection"]', function(event, button) {
        event.preventDefault();
        var root = button.closest('[data-connection-test]');
        var result = $('[data-role="result"]', root);
        var data = new FormData();
        data.append('option', 'com_ottomo');
        data.append('task', 'settings.testConnection');
        data.append('format', 'json');
        data.append(root.getAttribute('data-token'), '1');
        button.disabled = true;
        result.hidden = false;
        result.className = 'otm-status is-pending';
        result.textContent = '…';
        request('index.php', { data: data }).then(function(status) {
          result.className = 'otm-status is-' + status.level;
          result.textContent = status.message;
          // The default sender's domain: SPF, DKIM, DMARC (one line each)
          var checks = $('[data-role="auth"]', root);
          if (checks) {
            checks.remove();
          }
          if (status.auth && status.auth.checks && status.auth.checks.length) {
            checks = document.createElement('ul');
            checks.className = 'otm-auth-checks';
            checks.setAttribute('data-role', 'auth');
            status.auth.checks.forEach(function(check) {
              var item = document.createElement('li');
              item.className = 'otm-status is-' + check.level;
              item.textContent = check.message;
              checks.appendChild(item);
            });
            result.insertAdjacentElement('afterend', checks);
          }
        }).catch(function(error) {
          result.className = 'otm-status is-error';
          result.textContent = error.message;
        }).then(function() {
          button.disabled = false;
        });
      });
    }

    // ARIA tabs: [data-otm-tabs] > [role=tablist] > [role=tab][aria-controls] (click, arrows, Home/End)
    function setupAriaTabs() {
      $$('[data-otm-tabs]').forEach(function(root) {
        var tabs = $$('[role="tab"]', root);
        function select(tab, focus) {
          tabs.forEach(function(other) {
            var on = other === tab;
            other.setAttribute('aria-selected', on ? 'true' : 'false');
            other.tabIndex = on ? 0 : -1;
            var panel = document.getElementById(other.getAttribute('aria-controls'));
            if (panel) {
              panel.hidden = !on;
            }
          });
          if (focus) {
            tab.focus();
          }
        }
        tabs.forEach(function(tab, index) {
          tab.addEventListener('click', function() {
            select(tab, false);
          });
          tab.addEventListener('keydown', function(event) {
            var next = {ArrowRight: index + 1, ArrowLeft: index - 1, Home: 0, End: tabs.length - 1}[event.key];
            if (next === undefined) {
              return;
            }
            event.preventDefault();
            select(tabs[(next + tabs.length) % tabs.length], true);
          });
        });
      });
    }

    // Settings > Permissions: Joomla's script saves each change at once and works out the asset from the URL, which it
    // only knows for com_config (view=component&component=...). Answer as com_config would for Ottomo while it reads it.
    function setupPermissions() {
      var root = $('.otm-setting-rules');
      if (!root || !window.CMS || !CMS.permissions || !CMS.permissions.send) {
        return;
      }
      $$('select[onchange]', root).forEach(function(select) {
        select.onchange = null; // the inline sendPermissions() call: handled below instead
      });
      var params = { option: 'com_config', view: 'component', component: 'com_ottomo' };
      on(root, 'change', 'select', function(event, select) {
        var urlParam = CMS.permissions.urlParam;
        CMS.permissions.urlParam = function(name) {
          return Object.prototype.hasOwnProperty.call(params, name) ? params[name] : false;
        };
        try {
          CMS.permissions.send(select);
        } finally {
          CMS.permissions.urlParam = urlParam;
        }
      });
    }

    // Templates page: Duplicate and Delete act on one card (the confirmation of Delete is in layouts/templates.php)
    function setupTemplateActions() {
      on(document, 'click', '[data-template-task]', function(event, button) {
        var field = $('#adminForm [data-role="template-id"]');
        if (!field || !window.Joomla) {
          return;
        }
        event.preventDefault();
        field.value = button.getAttribute('data-id');
        field.disabled = false;
        if (Joomla.submitbutton(button.getAttribute('data-template-task')) === false) {
          field.disabled = true;
        }
      });
    }

    // Templates: choosing a package file installs it (the native file input stays hidden behind its button)
    function setupTemplateUpload() {
      var input = $('[data-role="template-package"]');
      if (!input) {
        return;
      }
      var label = input.parentNode.querySelector('label[for="' + input.id + '"]');
      if (label) {
        label.addEventListener('keydown', function(event) {
          if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            input.click();
          }
        });
      }
      input.addEventListener('change', function() {
        if (!input.files || !input.files.length) {
          return;
        }
        var name = $('[data-role="template-package-name"]');
        if (name) {
          name.textContent = input.files[0].name;
        }
        if (label) {
          label.classList.add('is-busy');
        }
        Joomla.submitbutton('templates.install');
      });
    }

    function boot() {
      if (!document.getElementById('otm')) {
        return; // not an Ottomo page (e.g. the control panel module)
      }
      setupConnectionTest();
      setupFormRules();
      setupTemplateUpload();
      setupTemplateActions();
      setupPermissions();
      setupAriaTabs();
      var value = function(name) {
        var input = $('#otm-admin-container input[name="' + name + '"]');
        return input ? input.value : '';
      };
      var view = value('view');
      var task = value('task');

      setupLists(task);
      setupEditorTabs();
      Ottomo.tabs();
      if (view === 'newsletters' && task === 'edit') {
        Ottomo.newsletter();
      } else if (view === 'extension') {
        Ottomo.extension();
      }
      // Import, export, send and batch run in SuperModal frames: see the Modals section
      setupTopicsSelection(view);
      setupMessages();
      setupModals();
      setupNavigation();
    }

    onReady(boot);
  })();


  /* Campaign wizard: personalisation, preview text, steps, pre-flight checklist
  -------------------------------------------------- */
  (function() {
    var MAX_PREHEADER = 150;
    var ICONS = {
      ok: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" class="otm-icon"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>',
      fail: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" class="otm-icon"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293z"/></svg>',
      step: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" class="bi bi-check-lg otm-icon"><path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425z"/></svg>',
      warn: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" class="otm-icon"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4m.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2"/></svg>'
    };

    function form() {
      return document.getElementById('adminForm');
    }

    function value(name) {
      var field = form() && form().elements[name];
      return field ? String(field.value || '').trim() : '';
    }

    function designHtml() {
      var frame = document.getElementById('otm-designer-frame');
      try {
        return frame && frame.contentDocument ? frame.contentDocument.documentElement.innerHTML : '';
      } catch (e) {
        return '';
      }
    }

    function testKey() {
      return 'ottomo-test-sent-' + value('id');
    }

    function testSent() {
      try {
        return !!value('id') && window.sessionStorage.getItem(testKey()) === '1';
      } catch (e) {
        return false;
      }
    }

    // ------------------------------------------------------------------ personalisation buttons

    function insertToken(button) {
      var input = document.getElementById(button.getAttribute('data-insert-target'));
      var token = button.getAttribute('data-insert-token');
      if (!input || !token) {
        return;
      }
      var start = typeof input.selectionStart === 'number' ? input.selectionStart : input.value.length;
      var end = typeof input.selectionEnd === 'number' ? input.selectionEnd : input.value.length;
      var before = input.value.slice(0, start);
      var spacer = before && !/\s$/.test(before) ? ' ' : '';
      input.value = before + spacer + token + input.value.slice(end);
      var caret = (before + spacer + token).length;
      input.focus();
      input.setSelectionRange(caret, caret);
      input.dispatchEvent(new Event('input', { bubbles: true }));
      input.dispatchEvent(new Event('change', { bubbles: true }));
    }

    // ------------------------------------------------------------------ checks

    function checks() {
      var root = document.querySelector('[data-preflight]');
      var footer = root && root.getAttribute('data-footer') === '1';
      var html = designHtml();
      var recipients = parseInt((document.getElementById('otm-total-recipients') || {}).textContent, 10) || 0;
      // The sender is optional: empty fields use Settings > Sending (their placeholder)
      var effective = function(name) {
        var field = form() && form().elements[name];
        return value(name) || (field ? String(field.getAttribute('placeholder') || '').trim() : '');
      };
      var email = effective('senderEmail');
      return {
        subject: value('subject') !== '',
        sender: effective('senderName') !== '' && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email),
        recipients: recipients > 0,
        saved: value('id') !== '' && !Ottomo.newsletterModified,
        unsubscribe: footer || /\{\{unsubscribe\}\}|subscribers\.unsubscribe/i.test(html),
        template: parseInt(value('template'), 10) > 0,
        preheader: value('preheader') !== '',
        test: testSent(),
        size: sizeCheck(),
        auth: authCheck(email)
      };
    }

    // Gmail clips emails over 102 KB: the saved campaign's size as sent, asked of the server after each save
    var size = { id: null, bytes: 0, limit: 102400 };
    function sizeCheck() {
      var id = value('id');
      var detail = document.querySelector('[data-role="size-detail"]');
      if (!id) {
        return true; // not saved yet: nothing to measure
      }
      if (size.id !== id && size.id !== 'pending:' + id) {
        size.id = 'pending:' + id;
        request('index.php', { params: { option: 'com_ottomo', task: 'newsletters.size', format: 'json', id: id } })
          .then(function(answer) {
            size = { id: id, bytes: parseInt(answer.bytes, 10) || 0, limit: parseInt(answer.limit, 10) || 102400 };
            Ottomo.preflight.run();
          })
          .catch(function() { size.id = id; });
        return null;
      }
      var kb = Math.ceil(size.bytes / 1024);
      var over = size.bytes > size.limit;
      if (detail && size.id === id) {
        detail.textContent = over
          ? text('COM_OTTOMO_PREFLIGHT_SIZE_OVER', 'Now %s KB: Gmail would hide the end of it, the unsubscribe link included. Shorten the content.').replace('%s', kb)
          : text('COM_OTTOMO_PREFLIGHT_SIZE_NOW', 'Now %s KB.').replace('%s', kb);
      }
      return !over;
    }

    // SPF, DKIM and DMARC of the sender's domain (Gmail, Yahoo and Outlook require them of bulk senders)
    var auth = {};
    function authCheck(email) {
      var detail = document.querySelector('[data-role="auth-detail"]');
      email = String(email || '').toLowerCase();
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        if (detail) {
          detail.textContent = '';
        }
        return false;
      }
      var domain = email.split('@').pop();
      if (!auth[domain]) {
        auth[domain] = { pending: true };
        request('index.php', { params: { option: 'com_ottomo', task: 'newsletters.senderCheck', format: 'json', email: email } })
          .then(function(answer) {
            auth[domain] = answer;
            Ottomo.preflight.run();
          })
          .catch(function() { delete auth[domain]; });
      }
      var result = auth[domain];
      if (detail) {
        detail.textContent = result.pending
          ? text('COM_OTTOMO_PREFLIGHT_CHECKING', 'Checking…')
          : (result.checks || []).filter(function(check) { return check.level !== 'ok'; }).map(function(check) { return check.message; }).join(' ');
      }
      return result.pending ? null : result.level === 'ok';
    }

    function renderChecklist(state) {
      var root = document.querySelector('[data-preflight]');
      if (!root) {
        return true;
      }
      var blocked = false;
      var warned = false;
      Array.prototype.forEach.call(root.querySelectorAll('[data-check]'), function(item) {
        var name = item.getAttribute('data-check');
        var blocking = item.getAttribute('data-blocking') === '1';
        var passed = !!state[name];
        var status = passed ? 'ok' : (blocking ? 'fail' : 'warn');
        if (!item.querySelector('.otm-preflight__icon')) {
          item.insertAdjacentHTML('afterbegin', '<span class="otm-preflight__icon"></span>');
        }
        item.querySelector('.otm-preflight__icon').innerHTML = ICONS[status];
        item.className = 'is-' + status;
        item.setAttribute('data-status', status);
        blocked = blocked || (blocking && !passed);
        warned = warned || (!blocking && !passed);
      });
      var summary = root.querySelector('[data-role="preflight-summary"]');
      if (summary) {
        summary.textContent = blocked
          ? text('COM_OTTOMO_PREFLIGHT_BLOCKED', 'Fix the items marked in red before sending.')
          : (warned ? text('COM_OTTOMO_PREFLIGHT_WARNINGS', 'Ready to send. The items in amber are recommended.') : text('COM_OTTOMO_PREFLIGHT_READY', 'All set: this campaign is ready to send.'));
        summary.className = 'otm-preflight__summary is-' + (blocked ? 'fail' : (warned ? 'warn' : 'ok'));
      }
      var required = root.getAttribute('data-required') === '1';
      var send = document.querySelector('[data-role="send-now"]');
      if (send) {
        send.disabled = required && blocked;
        send.setAttribute('aria-disabled', send.disabled ? 'true' : 'false');
      }
      return !blocked;
    }

    function renderSteps(state) {
      var complete = {
        '#step1': state.subject && state.sender && state.recipients,
        '#step2': state.template,
        '#step3': state.template && state.unsubscribe
      };
      Object.keys(complete).forEach(function(href) {
        var link = document.querySelector('[data-role="tabs-navigation"] a[href="' + href + '"]');
        if (!link) {
          return;
        }
        var num = link.querySelector('.num');
        if (num && !num.querySelector('.otm-step-check')) {
          num.insertAdjacentHTML('beforeend', '<span class="otm-step-check" aria-hidden="true">' + ICONS.step + '</span>');
        }
        link.classList.toggle('is-complete', !!complete[href]);
        var label = link.querySelector('.otm-step-state');
        if (!label) {
          link.insertAdjacentHTML('beforeend', '<span class="otm-step-state visually-hidden"></span>');
          label = link.querySelector('.otm-step-state');
        }
        label.textContent = complete[href] ? ' — ' + text('COM_OTTOMO_STEP_COMPLETE', 'complete') : '';
      });
    }

    function renderCounter() {
      var counter = document.querySelector('[data-role="preheader-count"]');
      if (counter) {
        counter.textContent = text('COM_OTTOMO_PREHEADER_COUNT', '%d of 150 characters').replace('%d', value('preheader').length).replace('150', MAX_PREHEADER);
      }
    }

    var preflight = Ottomo.preflight = {
      state: null,
      run: function() {
        this.state = checks();
        renderSteps(this.state);
        renderCounter();
        return renderChecklist(this.state);
      },
      ok: function() {
        var root = document.querySelector('[data-preflight]');
        var passed = this.run();
        return !root || root.getAttribute('data-required') !== '1' || passed;
      },
      markTestSent: function() {
        try {
          window.sessionStorage.setItem(testKey(), '1');
        } catch (e) { /* private mode */ }
        this.run();
      }
    };

    // ------------------------------------------------------------------ boot

    function boot() {
      if (!document.querySelector('[data-role="tabs-navigation"]')) {
        return;
      }
      var schedule = (function() {
        var timer;
        return function() {
          window.clearTimeout(timer);
          timer = window.setTimeout(function() {
            preflight.run();
          }, 120);
        };
      })();

      document.addEventListener('click', function(event) {
        var button = event.target.closest && event.target.closest('[data-insert-token]');
        if (button) {
          event.preventDefault();
          insertToken(button);
        }
        if (event.target.closest && event.target.closest('[data-role="tabs-navigation"] a, [data-role="tabs-next"], [data-role="tabs-previous"], [data-role="template"]')) {
          schedule();
        }
      });
      ['input', 'change'].forEach(function(type) {
        document.addEventListener(type, schedule);
      });
      document.addEventListener('ottomo:recipients', schedule);
      var frame = document.getElementById('otm-designer-frame');
      if (frame) {
        frame.addEventListener('load', schedule);
      }
      preflight.run();
    }

    onReady(boot);
  })();


  /* Designer: formatting toolbar, link bar, drag-and-drop (inside the designer iframe)
  -------------------------------------------------- */
  (function() {
    function elementOf(node) {
      return node && node.nodeType === 1 ? node : node && node.parentElement;
    }

    /** The [contenteditable="true"] region containing node (the outermost one), or null. */
    function editableOf(node) {
      var el = elementOf(node);
      var editable = null;
      while (el && el.nodeType === 1) {
        if (el.getAttribute('contenteditable') === 'true') {
          editable = el;
        }
        el = el.parentElement;
      }
      return editable;
    }

    var refreshTimer;

    /** Mark the newsletter as modified and refresh the HTML tab (debounced). */
    function changed() {
      Ottomo.newsletterModified = true;
      window.clearTimeout(refreshTimer);
      refreshTimer = window.setTimeout(function() {
        if (Ottomo.updateSourceEditor && Ottomo.sourceEditor) {
          Ottomo.updateSourceEditor();
        }
      }, 400);
    }

    // ------------------------------------------------------------------ editor

    var STATE_COMMANDS = ['bold', 'italic', 'underline', 'insertOrderedList', 'insertUnorderedList', 'justifyLeft', 'justifyCenter', 'justifyRight'];
    var BLOCKS = ['p', 'h1', 'h2', 'h3', 'h4', 'pre'];

    var editor = Ottomo.editor = {
      range: null,   // last selection inside an editable region of the iframe
      link: null,    // <a> being edited in the link bar
      image: null,   // <img> being edited in the image bar

      /** The saved range, if it is still inside an editable region. */
      current: function() {
        var doc = frameDocument();
        if (!doc || !this.range || !doc.contains(this.range.commonAncestorContainer)) {
          return null;
        }
        return editableOf(this.range.commonAncestorContainer) ? this.range : null;
      },

      /** Give focus and the saved selection back to the iframe. */
      restore: function() {
        var range = this.current();
        var doc = frameDocument();
        if (!range) {
          return false;
        }
        var editable = editableOf(range.commonAncestorContainer);
        doc.defaultView.focus();
        if (editable && doc.activeElement !== editable) {
          editable.focus({ preventScroll: true });
        }
        var selection = doc.getSelection();
        selection.removeAllRanges();
        selection.addRange(range);
        return true;
      },

      /**
       * Font of the selection: an email-safe stack as an inline font-family (what email clients support),
       * or '' for the template's own font (removes the font-family set on the selection).
       */
      font: function(stack) {
        if (!this.restore()) {
          return;
        }
        var doc = frameDocument();
        if (stack) {
          doc.execCommand('styleWithCSS', false, true);
          doc.execCommand('fontName', false, stack);
          doc.execCommand('styleWithCSS', false, false); // back to <b>/<i>/<u> for the other commands
        } else {
          var selection = doc.getSelection();
          var range = selection && selection.rangeCount ? selection.getRangeAt(0) : null;
          var editable = range ? editableOf(elementOf(range.commonAncestorContainer)) : null;
          if (editable) {
            var nodes = [elementOf(range.commonAncestorContainer)].concat(Array.prototype.slice.call(editable.querySelectorAll('[style*="font-family"], font[face]')));
            nodes.forEach(function(node) {
              if (!node || node === editable || !editable.contains(node) || !range.intersectsNode(node)) {
                return;
              }
              if (node.style) {
                node.style.removeProperty('font-family');
                if (!node.getAttribute('style')) {
                  node.removeAttribute('style');
                }
              }
              node.removeAttribute('face');
              // a span or font left without attributes is only a wrapper: unwrap it
              if (/^(SPAN|FONT)$/.test(node.tagName) && !node.attributes.length) {
                while (node.firstChild) {
                  node.parentNode.insertBefore(node.firstChild, node);
                }
                node.parentNode.removeChild(node);
              }
            });
          }
        }
        this.save();
        changed();
        this.refresh();
      },

      exec: function(command, value) {
        if (!this.restore()) {
          return;
        }
        var doc = frameDocument();
        if (command === 'formatBlock') {
          value = '<' + value + '>';
        }
        doc.execCommand(command, false, value === undefined ? null : value);
        this.save();
        changed();
        this.refresh();
      },

      save: function() {
        var doc = frameDocument();
        var selection = doc && doc.getSelection();
        if (selection && selection.rangeCount && editableOf(selection.anchorNode)) {
          this.range = selection.getRangeAt(0).cloneRange();
        }
      },

      /** Update pressed states, the block format and the link bar from the current selection. */
      refresh: function() {
        var doc = frameDocument();
        var bar = document.querySelector('[data-editor-toolbar]');
        if (!doc || !bar) {
          return;
        }
        var range = this.current();
        var active = !!range;
        bar.classList.toggle('is-inactive', !active);
        STATE_COMMANDS.forEach(function(command) {
          var button = bar.querySelector('[data-command="' + command + '"]');
          if (button) {
            var pressed = false;
            try {
              pressed = active && doc.queryCommandState(command);
            } catch (e) { /* ignore */ }
            button.setAttribute('aria-pressed', pressed ? 'true' : 'false');
          }
        });
        var select = bar.querySelector('select[data-command="formatBlock"]');
        if (select) {
          var block = '';
          try {
            block = active ? String(doc.queryCommandValue('formatBlock') || '').toLowerCase().replace(/[<>]/g, '') : '';
          } catch (e) { /* ignore */ }
          select.value = BLOCKS.indexOf(block) > -1 ? block : 'p';
          select.disabled = !active;
        }
        var fontSelect = bar.querySelector('select[data-command="fontName"]');
        if (fontSelect) {
          // The font set on the selection itself (inline), not the template's: else "Template font"
          var styled = active ? elementOf(range.startContainer).closest('[style*="font-family"], font[face]') : null;
          var current = styled && editableOf(styled) && styled !== editableOf(styled) ? (styled.style.fontFamily || styled.getAttribute('face') || '') : '';
          var first = function(stack) {
            return String(stack).split(',')[0].replace(/["']/g, '').trim().toLowerCase();
          };
          fontSelect.value = '';
          Array.prototype.forEach.call(fontSelect.options, function(option) {
            if (current && option.value && first(option.value) === first(current)) {
              fontSelect.value = option.value;
            }
          });
          fontSelect.disabled = !active;
        }
        var anchor = active ? elementOf(range.startContainer).closest('a') : null;
        if (anchor && this.image && anchor.contains(this.image)) {
          anchor = null; // the image bar edits the link of an image
        }
        this.showLink(anchor && editableOf(anchor) ? anchor : null);
      },

      showLink: function(anchor, focus) {
        var linkBar = document.querySelector('[data-editor-link]');
        if (!linkBar) {
          return;
        }
        this.link = anchor;
        linkBar.hidden = !anchor;
        var button = document.querySelector('[data-editor-toolbar] [data-command="link"]');
        if (button) {
          button.setAttribute('aria-pressed', anchor ? 'true' : 'false');
        }
        if (!anchor) {
          return;
        }
        var href = anchor.getAttribute('href') || '';
        var input = linkBar.querySelector('[data-link-href]');
        if (document.activeElement !== input) {
          input.value = href === '#' ? '' : href;
        }
        linkBar.querySelector('[data-link-target]').setAttribute('aria-pressed', anchor.getAttribute('target') === '_blank' ? 'true' : 'false');
        linkBar.querySelector('[data-link-follow]').setAttribute('href', href || '#');
        if (focus) {
          input.focus();
          input.select();
        }
      },

      /** Link button: edit the link at the caret, or turn the selected text into a link. */
      createLink: function() {
        var range = this.current();
        if (!range) {
          return;
        }
        var anchor = elementOf(range.startContainer).closest('a');
        if (!anchor && !range.collapsed) {
          this.exec('createLink', '#otm-new-link');
          var doc = frameDocument();
          anchor = doc.querySelector('a[href="#otm-new-link"]');
          if (anchor) {
            anchor.setAttribute('href', '');
          }
        }
        if (anchor) {
          this.showLink(anchor, true);
        }
      },

      removeLink: function() {
        var anchor = this.link;
        if (!anchor || !anchor.parentNode) {
          return;
        }
        while (anchor.firstChild) {
          anchor.parentNode.insertBefore(anchor.firstChild, anchor);
        }
        anchor.parentNode.removeChild(anchor);
        this.showLink(null);
        changed();
      },

      // ---------------------------------------------------------------- images

      /**
       * The width an image may take: its column in the email, the table cell around it (the designer's own block
       * frames are narrower), else its position.
       */
      imageRoom: function(img) {
        var box = img.closest('td') || img.closest('.otm-position, .otm-position-content, .otm-sample-data, [contenteditable="true"]') || img.parentElement;
        var style = box.ownerDocument.defaultView.getComputedStyle(box);
        return Math.max(16, Math.floor(box.clientWidth - (parseFloat(style.paddingLeft) || 0) - (parseFloat(style.paddingRight) || 0)));
      },

      /**
       * Width in pixels: the width attribute (what classic Outlook uses) and max-width:100% with height:auto, so the
       * image keeps its proportions and never overflows a narrower column (phones).
       */
      setImageWidth: function(img, width) {
        width = Math.round(width);
        if (!width || width < 1) {
          return;
        }
        img.setAttribute('width', String(width));
        img.removeAttribute('height');
        img.style.removeProperty('width');
        img.style.removeProperty('height');
        img.style.maxWidth = '100%';
        img.style.height = 'auto';
      },

      /**
       * Fit images to their column: those without a width, or wider than their column. force: every image (the Fit
       * button). quiet: don't mark the campaign as changed (a designer that just opened).
       */
      fitImages: function(root, force, quiet) {
        var self = this;
        if (!root) {
          return;
        }
        var images = root.tagName === 'IMG' ? [root] : Array.prototype.slice.call(root.querySelectorAll('img'));
        images.forEach(function(img) {
          if (img.closest('.otm-extension-menu') || !img.closest('.otm-position-content, .otm-sample-data, [contenteditable="true"]')) {
            return;
          }
          var fit = function() {
            var natural = img.naturalWidth;
            if (!natural) {
              return;
            }
            var room = self.imageRoom(img);
            var current = parseInt(img.getAttribute('width'), 10) || 0;
            if (!force && current && current <= room) {
              return;
            }
            var width = Math.min(natural, room);
            if (width === current && img.style.maxWidth === '100%') {
              return;
            }
            self.setImageWidth(img, width);
            if (!quiet) {
              changed();
            }
            if (img === self.image) {
              self.showImage(img);
            }
          };
          if (img.complete && img.naturalWidth) {
            fit();
          } else {
            img.addEventListener('load', fit, { once: true });
          }
        });
      },

      /** The element that is aligned with an image: the link around it, or the image. */
      imageTarget: function(img) {
        var parent = img.parentElement;
        if (parent && parent.tagName === 'A' && parent.children.length === 1 && !parent.textContent.trim()) {
          return parent;
        }
        return img;
      },

      /** The block that aligns the image: its own wrapper, or the block it is alone in (not an editor wrapper). */
      imageBlock: function(img, create) {
        var target = this.imageTarget(img);
        var parent = target.parentElement;
        var alone = parent && /^(P|DIV|H[1-6]|TD|LI|SPAN)$/.test(parent.tagName) && parent.children.length === 1 && !parent.textContent.trim();
        var editorWrapper = parent && (parent.classList.contains('otm-position-content') || parent.classList.contains('otm-sample-data'));
        if (alone && !editorWrapper) {
          return parent;
        }
        if (!create || !parent) {
          return null;
        }
        // Inside text (a paragraph or a heading) only an inline element may wrap it
        var inline = /^(P|H[1-6]|LI|A|SPAN|B|I|U|STRONG|EM)$/.test(parent.tagName);
        var wrapper = img.ownerDocument.createElement(inline ? 'span' : 'div');
        if (inline) {
          wrapper.style.display = 'block';
        }
        parent.insertBefore(wrapper, target);
        wrapper.appendChild(target);
        return wrapper;
      },

      imageAlign: function(img) {
        var block = this.imageBlock(img, false);
        var value = block ? (block.style.textAlign || block.getAttribute('align') || '') : '';
        return /^(left|center|right)$/.test(value) ? value : '';
      },

      setImageAlign: function(img, value) {
        var block = this.imageBlock(img, true);
        if (!block) {
          return;
        }
        // text-align for every client, align for classic Outlook; the image stays inline so that it follows them
        block.style.textAlign = value;
        block.setAttribute('align', value);
        if (img.style.display === 'block') {
          img.style.removeProperty('display');
        }
        changed();
        this.showImage(img);
      },

      /** The link around an image (only when it wraps the image alone). */
      imageLink: function(img) {
        var target = this.imageTarget(img);
        return target.tagName === 'A' ? target : null;
      },

      setImageLink: function(img, href) {
        var anchor = this.imageLink(img);
        if (href) {
          if (!anchor) {
            anchor = img.ownerDocument.createElement('a');
            img.parentNode.insertBefore(anchor, img);
            anchor.appendChild(img);
          }
          anchor.setAttribute('href', href);
        } else if (anchor) {
          anchor.parentNode.insertBefore(img, anchor);
          anchor.parentNode.removeChild(anchor);
        }
        changed();
      },

      /** Select an image (or none): the image bar and the outline around it in the designer. */
      showImage: function(img, focus) {
        var bar = document.querySelector('[data-editor-image]');
        this.image = img || null;
        this.outlineImage();
        if (!bar) {
          return;
        }
        bar.hidden = !img;
        if (!img) {
          return;
        }
        this.showLink(null);
        var width = bar.querySelector('[data-image-width]');
        if (document.activeElement !== width) {
          width.value = img.getAttribute('width') || (img.naturalWidth ? Math.min(img.naturalWidth, this.imageRoom(img)) : '');
        }
        var align = this.imageAlign(img);
        $$('[data-image-align]', bar).forEach(function(button) {
          button.setAttribute('aria-pressed', button.getAttribute('data-image-align') === align ? 'true' : 'false');
        });
        var alt = bar.querySelector('[data-image-alt]');
        if (document.activeElement !== alt) {
          alt.value = img.getAttribute('alt') || '';
        }
        var anchor = this.imageLink(img);
        var href = bar.querySelector('[data-image-href]');
        if (document.activeElement !== href) {
          href.value = anchor ? (anchor.getAttribute('href') || '') : '';
        }
        var target = bar.querySelector('[data-image-target]');
        target.disabled = !anchor;
        target.setAttribute('aria-pressed', anchor && anchor.getAttribute('target') === '_blank' ? 'true' : 'false');
        if (focus) {
          width.focus();
          width.select();
        }
      },

      /** Outline of the selected image, drawn over the designer (not part of the email). */
      outlineImage: function() {
        var doc = frameDocument();
        if (!doc || !doc.body) {
          return;
        }
        var outline = doc.querySelector('.otm-image-outline');
        if (!this.image || !doc.contains(this.image)) {
          if (outline) {
            outline.parentNode.removeChild(outline);
          }
          return;
        }
        if (!outline) {
          outline = doc.createElement('div');
          outline.className = 'otm-image-outline';
          outline.setAttribute('aria-hidden', 'true');
          doc.body.appendChild(outline);
        }
        var rect = this.image.getBoundingClientRect();
        var view = doc.defaultView;
        outline.style.top = (rect.top + view.pageYOffset) + 'px';
        outline.style.left = (rect.left + view.pageXOffset) + 'px';
        outline.style.width = rect.width + 'px';
        outline.style.height = rect.height + 'px';
      },

      /** Bind the toolbar and link bar of the admin page (once). */
      bindToolbar: function() {
        var bar = document.querySelector('[data-editor-toolbar]');
        var linkBar = document.querySelector('[data-editor-link]');
        if (!bar || bar.ottomoBound) {
          return;
        }
        bar.ottomoBound = true;
        var self = this;

        // Keep the iframe selection: buttons must not take the focus on mousedown
        bar.addEventListener('mousedown', function(event) {
          if (event.target.closest('button')) {
            event.preventDefault();
          }
        });
        bar.addEventListener('click', function(event) {
          var button = event.target.closest('button[data-command]');
          if (!button) {
            return;
          }
          event.preventDefault();
          var command = button.getAttribute('data-command');
          if (command === 'link') {
            self.createLink();
          } else {
            self.exec(command);
          }
        });
        bar.addEventListener('change', function(event) {
          if (event.target.matches('select[data-command="formatBlock"]')) {
            self.exec('formatBlock', event.target.value);
          }
          if (event.target.matches('select[data-command="fontName"]')) {
            self.font(event.target.value);
          }
          // Personalisation: insert a subscriber placeholder ({{firstname|there}}…) at the caret
          if (event.target.matches('select[data-command="insertField"]') && event.target.value) {
            self.exec('insertText', event.target.value);
            event.target.value = '';
          }
        });

        if (linkBar) {
          var input = linkBar.querySelector('[data-link-href]');
          input.addEventListener('input', function() {
            if (self.link) {
              self.link.setAttribute('href', input.value.trim());
              linkBar.querySelector('[data-link-follow]').setAttribute('href', input.value.trim() || '#');
              changed();
            }
          });
          input.addEventListener('keydown', function(event) {
            if (event.key === 'Enter' || event.key === 'Escape') {
              event.preventDefault();
              self.restore();
            }
          });
          // An empty link is removed when leaving the field
          input.addEventListener('change', function() {
            if (self.link && !input.value.trim()) {
              self.removeLink();
            }
          });
          linkBar.querySelector('[data-link-target]').addEventListener('click', function(event) {
            event.preventDefault();
            if (!self.link) {
              return;
            }
            if (self.link.getAttribute('target') === '_blank') {
              self.link.removeAttribute('target');
              self.link.removeAttribute('rel');
            } else {
              self.link.setAttribute('target', '_blank');
              self.link.setAttribute('rel', 'noopener');
            }
            self.showLink(self.link);
            changed();
          });
          linkBar.querySelector('[data-link-remove]').addEventListener('click', function(event) {
            event.preventDefault();
            self.removeLink();
          });
        }

        var imageBar = document.querySelector('[data-editor-image]');
        if (imageBar) {
          var width = imageBar.querySelector('[data-image-width]');
          width.addEventListener('input', function() {
            var value = parseInt(width.value, 10);
            if (self.image && value >= 16) {
              self.setImageWidth(self.image, value);
              self.outlineImage();
              changed();
            }
          });
          width.addEventListener('change', function() {
            if (self.image) {
              self.showImage(self.image);
            }
          });
          imageBar.querySelector('[data-image-fit]').addEventListener('click', function(event) {
            event.preventDefault();
            if (self.image) {
              self.fitImages(self.image, true);
            }
          });
          $$('[data-image-align]', imageBar).forEach(function(button) {
            button.addEventListener('click', function(event) {
              event.preventDefault();
              if (self.image) {
                self.setImageAlign(self.image, button.getAttribute('data-image-align'));
              }
            });
          });
          var alt = imageBar.querySelector('[data-image-alt]');
          alt.addEventListener('input', function() {
            if (self.image) {
              self.image.setAttribute('alt', alt.value);
              changed();
            }
          });
          var href = imageBar.querySelector('[data-image-href]');
          href.addEventListener('input', function() {
            if (self.image) {
              self.setImageLink(self.image, href.value.trim());
              self.showImage(self.image);
            }
          });
          imageBar.querySelector('[data-image-target]').addEventListener('click', function(event) {
            event.preventDefault();
            var anchor = self.image && self.imageLink(self.image);
            if (!anchor) {
              return;
            }
            if (anchor.getAttribute('target') === '_blank') {
              anchor.removeAttribute('target');
              anchor.removeAttribute('rel');
            } else {
              anchor.setAttribute('target', '_blank');
              anchor.setAttribute('rel', 'noopener');
            }
            self.showImage(self.image);
            changed();
          });
          imageBar.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
              self.showImage(null);
            }
          });
        }
      },

      /** Bind the editable regions of the iframe document (once per document). */
      attach: function(doc) {
        if (!doc || doc.ottomoEditor) {
          return;
        }
        doc.ottomoEditor = true;
        var self = this;
        try {
          doc.execCommand('styleWithCSS', false, false);       // <b>/<i>/<u>, not inline styles
          doc.execCommand('defaultParagraphSeparator', false, 'p');
        } catch (e) { /* ignore */ }

        doc.addEventListener('selectionchange', function() {
          self.save();
          self.refresh();
        });
        doc.addEventListener('input', function(event) {
          if (editableOf(event.target)) {
            changed();
          }
        });
        // Copy and cut inside the email keep its HTML (images, links, formatting), marked as the designer's own
        var CLIP = 'application/x-ottomo-html';
        var CLIP_MARK = '<!--ottomo-clip-->';
        var detach = function(node) {
          node.parentNode.removeChild(node);
        };
        var copy = function(event, cut) {
          var selection = doc.getSelection();
          if (!event.clipboardData || !selection || !selection.rangeCount || selection.isCollapsed || !editableOf(selection.anchorNode)) {
            return;
          }
          var holder = doc.createElement('div');
          holder.appendChild(selection.getRangeAt(0).cloneContents());
          $$('.otm-image-outline', holder).forEach(detach);
          event.clipboardData.setData('text/html', CLIP_MARK + holder.innerHTML);
          event.clipboardData.setData('text/plain', selection.toString());
          try {
            event.clipboardData.setData(CLIP, '1');
          } catch (e) { /* the comment marks it too */ }
          event.preventDefault();
          if (cut) {
            doc.execCommand('delete');
            changed();
          }
        };
        doc.addEventListener('copy', function(event) {
          copy(event, false);
        });
        doc.addEventListener('cut', function(event) {
          copy(event, true);
        });
        // Paste: the designer's own content as HTML; anything from outside as plain text (no foreign styles in the email)
        doc.addEventListener('paste', function(event) {
          if (!editableOf(event.target) || !event.clipboardData) {
            return;
          }
          event.preventDefault();
          var html = event.clipboardData.getData('text/html') || '';
          var mark = html.indexOf(CLIP_MARK);
          var own = mark > -1 || Array.prototype.indexOf.call(event.clipboardData.types || [], CLIP) > -1;
          if (own && html) {
            var holder = doc.createElement('div');
            holder.innerHTML = html.slice(mark > -1 ? mark + CLIP_MARK.length : 0).replace(/<!--(Start|End)Fragment-->/g, '');
            // no editor markup and nothing that runs
            $$('script, style, link, meta, iframe, object, .otm-image-outline, .otm-extension-menu, .otm-nsl-btn', holder).forEach(detach);
            $$('*', holder).forEach(function(node) {
              node.removeAttribute('contenteditable');
              Array.prototype.slice.call(node.attributes).forEach(function(attribute) {
                if (/^on/i.test(attribute.name) || (/^(href|src)$/i.test(attribute.name) && /^\s*javascript:/i.test(attribute.value))) {
                  node.removeAttribute(attribute.name);
                }
              });
            });
            doc.execCommand('insertHTML', false, holder.innerHTML);
          } else {
            doc.execCommand('insertText', false, event.clipboardData.getData('text/plain'));
          }
          changed();
        });
        // Highlight the block being edited (hides its move/remove buttons, see designer.css)
        doc.addEventListener('focusin', function(event) {
          var editable = editableOf(event.target);
          if (editable) {
            var item = editable.closest('.otm-position-content, .otm-sample-data');
            if (item) {
              item.classList.add('otm-editing');
            }
          }
        });
        doc.addEventListener('focusout', function(event) {
          var item = event.target.closest && event.target.closest('.otm-editing');
          if (item) {
            item.classList.remove('otm-editing');
          }
        });
        // Links inside editable regions must not navigate the designer
        doc.addEventListener('click', function(event) {
          var anchor = event.target.closest && event.target.closest('a[href]');
          if (anchor && (editableOf(anchor) || anchor.closest('.otm-position-content'))) {
            event.preventDefault();
          }
        });
        // A click on an image of the email's content selects it (image bar); a click elsewhere drops it
        doc.addEventListener('click', function(event) {
          var img = event.target.closest && event.target.closest('img');
          if (img && !img.closest('.otm-extension-menu') && img.closest('.otm-position-content, .otm-sample-data, [contenteditable="true"]')) {
            self.showImage(img);
          } else if (self.image) {
            self.showImage(null);
          }
        });
        doc.defaultView.addEventListener('resize', function() {
          self.outlineImage();
        });
        doc.addEventListener('input', function() {
          self.outlineImage();
        });
        // Images wider than their column (inserted before Ottomo fitted them) are fitted when the designer opens
        self.fitImages(doc.body, false, true);
      }
    };

    // ------------------------------------------------------------------ sortables

    var sortables = Ottomo.sortables = {
      dragged: null,
      placeholder: null,

      move: function(item, before) {
        var container = item.parentNode;
        if (before === undefined) {
          return;
        }
        container.insertBefore(item, before);
        changed();
      },

      cleanup: function() {
        if (this.placeholder && this.placeholder.parentNode) {
          this.placeholder.parentNode.removeChild(this.placeholder);
        }
        if (this.dragged) {
          this.dragged.classList.remove('is-dragging');
          this.dragged.removeAttribute('draggable');
        }
        this.dragged = null;
      },

      attach: function(doc) {
        if (!doc || doc.ottomoSortables) {
          return;
        }
        doc.ottomoSortables = true;
        var self = this;

        // Drag only from the handle
        doc.addEventListener('mousedown', function(event) {
          var handle = event.target.closest && event.target.closest('.otm-sortable-handle');
          if (handle) {
            handle.closest('.otm-sortable-item').setAttribute('draggable', 'true');
          }
        });
        doc.addEventListener('mouseup', function(event) {
          var item = event.target.closest && event.target.closest('.otm-sortable-item[draggable]');
          if (item && item !== self.dragged) {
            item.removeAttribute('draggable');
          }
        });
        doc.addEventListener('dragstart', function(event) {
          var item = event.target.closest && event.target.closest('.otm-sortable-item[draggable="true"]');
          if (!item) {
            return;
          }
          self.dragged = item;
          self.placeholder = doc.createElement('div');
          self.placeholder.className = 'otm-sortable-placeholder';
          self.placeholder.style.height = item.offsetHeight + 'px';
          event.dataTransfer.effectAllowed = 'move';
          event.dataTransfer.setData('text/plain', ''); // required by Firefox
          window.setTimeout(function() {
            item.classList.add('is-dragging');
          }, 0);
        });
        doc.addEventListener('dragover', function(event) {
          if (!self.dragged) {
            return;
          }
          var container = event.target.closest && event.target.closest('.otm-sortable');
          if (!container) {
            return;
          }
          event.preventDefault();
          event.dataTransfer.dropEffect = 'move';
          var items = Array.prototype.filter.call(container.children, function(child) {
            return child.classList.contains('otm-sortable-item') && child !== self.dragged;
          });
          var before = null;
          for (var i = 0; i < items.length; i++) {
            var box = items[i].getBoundingClientRect();
            if (event.clientY < box.top + box.height / 2) {
              before = items[i];
              break;
            }
          }
          if (self.placeholder.parentNode !== container || self.placeholder.nextSibling !== before) {
            container.insertBefore(self.placeholder, before);
          }
        });
        doc.addEventListener('drop', function(event) {
          if (!self.dragged || !self.placeholder.parentNode) {
            return;
          }
          event.preventDefault();
          self.placeholder.parentNode.insertBefore(self.dragged, self.placeholder);
          self.cleanup();
          changed();
        });
        doc.addEventListener('dragend', function() {
          self.cleanup();
        });

        // Keyboard: arrow keys on a focused handle move the block; Enter/Space activate the remove button
        doc.addEventListener('keydown', function(event) {
          var handle = event.target.closest && event.target.closest('.otm-sortable-handle');
          if (handle && (event.key === 'ArrowUp' || event.key === 'ArrowDown')) {
            event.preventDefault();
            var item = handle.closest('.otm-sortable-item');
            var sibling = event.key === 'ArrowUp' ? item.previousElementSibling : item.nextElementSibling;
            while (sibling && !sibling.classList.contains('otm-sortable-item')) {
              sibling = event.key === 'ArrowUp' ? sibling.previousElementSibling : sibling.nextElementSibling;
            }
            if (sibling) {
              self.move(item, event.key === 'ArrowUp' ? sibling : sibling.nextSibling);
              handle.focus();
            }
          }
        });
      }
    };

    /** Desktop / mobile preview width of the designer (editor head toggle). */
    function bindDeviceToggle() {
      var group = document.querySelector('.otm-device-toggle');
      if (!group || group.ottomoBound) {
        return;
      }
      group.ottomoBound = true;
      group.addEventListener('click', function(event) {
        var button = event.target.closest('button[data-device]');
        if (!button) {
          return;
        }
        var mobile = button.getAttribute('data-device') === 'mobile';
        Array.prototype.forEach.call(group.querySelectorAll('button[data-device]'), function(other) {
          other.setAttribute('aria-pressed', other === button ? 'true' : 'false');
        });
        var frame = document.getElementById('otm-designer-frame');
        if (frame) {
          frame.classList.toggle('is-mobile', mobile);
        }
      });
    }

    /** Bind editor + sortables to the designer iframe's current document. */
    Ottomo.designerAttach = function() {
      bindDeviceToggle();
      var doc = frameDocument();
      Ottomo.emailScheme(doc, false); // designed in light mode
      editor.bindToolbar();
      editor.attach(doc);
      sortables.attach(doc);
      editor.refresh();
    };
  })();


  /* Statistics: dashboard and control panel module
  -------------------------------------------------- */
  (function() {
    /** YYYY-MM-DD in local time. */
    function ymd(date) {
      var pad = function (n) { return (n < 10 ? '0' : '') + n; };
      return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate());
    }

    /** Today minus one day, week or month (months clamp to the end of the shorter month, like moment.js). */
    function rangeStart(type) {
      var date = new Date();
      if (type === 'days') {
        date.setDate(date.getDate() - 1);
      } else if (type === 'weeks') {
        date.setDate(date.getDate() - 7);
      } else {
        var day = date.getDate();
        date.setDate(1);
        date.setMonth(date.getMonth() - 1);
        date.setDate(Math.min(day, new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate()));
      }
      return ymd(date);
    }

    onReady(function () {
      var form = document.getElementById('otm-statistics-form');
      if (!form) {
        return;
      }
      var ranges = Array.prototype.slice.call(document.querySelectorAll('.otm-statistics-range'));
      var setDates = function (start) {
        document.getElementById('startDate').value = start;
        document.getElementById('endDate').value = ymd(new Date());
      };

      setDates(rangeStart('months'));
      renderChart();

      var timeout;
      window.addEventListener('resize', function () {
        clearTimeout(timeout);
        timeout = setTimeout(renderChart, 300);
      });

      var press = function (active) {
        ranges.forEach(function (range) {
          range.classList.toggle('otm-active', range === active);
          range.setAttribute('aria-pressed', range === active ? 'true' : 'false');
        });
      };

      // Custom dates: no preset range is active any more
      document.getElementById('otm-statistics-form-apply').addEventListener('click', function (event) {
        event.preventDefault();
        press(null);
        if (form.elements.range) {
          form.elements.range.value = 0;
        }
        renderChart();
      });

      // Preset ranges stay highlighted
      ranges.forEach(function (range) {
        range.addEventListener('click', function (event) {
          event.preventDefault();
          var value = range.getAttribute('data-range');
          setDates(rangeStart(value === 'day' ? 'days' : (value === 'week' ? 'weeks' : 'months')));
          press(range);
          if (form.elements.range) {
            form.elements.range.value = 0;
          }
          renderChart();
        });
      });
    });

    function renderChart() {
      var form = document.getElementById('otm-statistics-form');
      var chart = document.getElementById('otm-chart');
      var params = new URLSearchParams(new FormData(form));
      request('index.php', { params: params }).then(function (response) {
        var primary = document.querySelector('[data-role="stats-primary"]');
        var secondary = document.querySelector('[data-role="stats-secondary"]');
        var colors = [];
        var stats = response.stats || {};
        if (primary) {
          primary.textContent = '';
        }
        if (secondary) {
          secondary.textContent = '';
        }

        var el = function (tag, className, text) {
          var node = document.createElement(tag);
          node.className = className;
          if (text !== undefined) {
            node.textContent = text;
          }
          return node;
        };

        // Main figures (sent/requests, opened, clicked) as KPI cards; anything else as a compact list
        Object.keys(stats).forEach(function (key, index) {
          var entry = stats[key];
          var card;
          if (index < 3) {
            card = el('div', 'otm-stats__kpi');
            card.style.setProperty('--kpi-color', entry.color);
            var label = el('div', 'otm-stats__label');
            label.appendChild(el('span', 'otm-stats__dot'));
            label.appendChild(document.createTextNode(entry.label));
            card.appendChild(label);
            if (index === 0) {
              card.appendChild(el('div', 'otm-stats__value', String(entry.total)));
            } else {
              card.appendChild(el('div', 'otm-stats__value', entry.percentage + '%'));
              card.appendChild(el('div', 'otm-stats__meta', String(entry.total)));
            }
            if (primary) {
              primary.appendChild(card);
            }
          } else {
            card = el('div', 'otm-stats__item');
            card.style.setProperty('--kpi-color', entry.color);
            card.appendChild(el('span', 'otm-stats__dot'));
            card.appendChild(el('span', 'otm-stats__item-label', entry.label));
            card.appendChild(el('span', 'otm-stats__item-value', entry.percentage + '%'));
            if (secondary) {
              secondary.appendChild(card);
            }
          }
          colors.push(entry.color);
        });

        if (secondary) {
          secondary.hidden = !secondary.children.length;
        }

        renderSvgChart(chart, response.chart || [], colors);
      }).catch(function (error) {
        if (chart) {
          chart.textContent = error.message;
        }
      });
    }

    function renderSvgChart(container, chartRows, colors) {
      if (!container) {
        return;
      }
      if (!chartRows || chartRows.length < 2) {
        container.innerHTML = '<div class="otm-chart-empty">No statistics data available for the selected range.</div>';
        return;
      }

      var headers = chartRows[0];
      var rows = chartRows.slice(1);
      // ISO days -> short local dates ("Oct 23"; with the year when the range spans years). Other labels as they are.
      var years = {};
      rows.forEach(function (r) { years[String(r[0]).slice(0, 4)] = true; });
      var dateFormat = null;
      try {
        dateFormat = new Intl.DateTimeFormat(document.documentElement.lang || undefined, Object.keys(years).length > 1 ? { month: 'short', day: 'numeric', year: 'numeric', timeZone: 'UTC' } : { month: 'short', day: 'numeric', timeZone: 'UTC' });
      } catch (e) { /* old browser: ISO */ }
      var dayLabel = function (value) {
        var text = String(value);
        var m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(text.slice(0, 10));
        return m && dateFormat ? dateFormat.format(new Date(Date.UTC(+m[1], m[2] - 1, +m[3]))) : text.slice(0, 10);
      };
      var numSeries = headers.length - 1;
      var width = Math.max(container.clientWidth || 640, 280); // real width: labels keep their size on narrow screens
      // Height: the room the layout gives the container (the dashboard stretches it to the side column), at least 220px.
      // Measured empty, so the previous chart does not hold the container open.
      container.innerHTML = '';
      var height = Math.max(220, Math.floor(container.clientHeight || 0));
      var pad = { top: 20, right: 20, bottom: 38, left: 46 };
      var plotW = width - pad.left - pad.right;
      var plotH = height - pad.top - pad.bottom;

      var maxVal = 0;
      rows.forEach(function (r) {
        for (var s = 1; s < r.length; s++) {
          var v = Number(r[s]) || 0;
          if (v > maxVal) {
            maxVal = v;
          }
        }
      });
      maxVal = maxVal <= 0 ? 4 : Math.ceil(maxVal * 1.15);

      var ns = 'http://www.w3.org/2000/svg';
      var svg = document.createElementNS(ns, 'svg');
      svg.setAttribute('viewBox', '0 0 ' + width + ' ' + height);
      svg.setAttribute('class', 'otm-svg-chart');
      svg.setAttribute('role', 'img');
      svg.setAttribute('aria-label', 'Campaign statistics chart');

      var defs = document.createElementNS(ns, 'defs');
      for (var s = 0; s < numSeries; s++) {
        var color = colors[s] || '#4f46e5';
        var grad = document.createElementNS(ns, 'linearGradient');
        grad.setAttribute('id', 'otm-grad-' + s);
        grad.setAttribute('x1', '0');
        grad.setAttribute('y1', '0');
        grad.setAttribute('x2', '0');
        grad.setAttribute('y2', '1');
        var stop1 = document.createElementNS(ns, 'stop');
        stop1.setAttribute('offset', '0%');
        stop1.setAttribute('stop-color', color);
        stop1.setAttribute('stop-opacity', '0.18');
        var stop2 = document.createElementNS(ns, 'stop');
        stop2.setAttribute('offset', '100%');
        stop2.setAttribute('stop-color', color);
        stop2.setAttribute('stop-opacity', '0.0');
        grad.appendChild(stop1);
        grad.appendChild(stop2);
        defs.appendChild(grad);
      }
      svg.appendChild(defs);

      // Horizontal gridlines & Y labels
      var ticks = 4;
      for (var t = 0; t <= ticks; t++) {
        var yVal = Math.round((maxVal / ticks) * t);
        var yPos = pad.top + plotH - (plotH * t) / ticks;
        var line = document.createElementNS(ns, 'line');
        line.setAttribute('x1', pad.left);
        line.setAttribute('x2', pad.left + plotW);
        line.setAttribute('y1', yPos);
        line.setAttribute('y2', yPos);
        line.setAttribute('class', 'otm-chart-grid');
        svg.appendChild(line);

        var label = document.createElementNS(ns, 'text');
        label.setAttribute('x', pad.left - 8);
        label.setAttribute('y', yPos + 4);
        label.setAttribute('text-anchor', 'end');
        label.setAttribute('class', 'otm-chart-label');
        label.textContent = yVal;
        svg.appendChild(label);
      }

      function xFor(idx) {
        if (rows.length === 1) {
          return pad.left + plotW / 2;
        }
        return pad.left + (plotW * idx) / (rows.length - 1);
      }
      function yFor(val) {
        return pad.top + plotH - (plotH * (Number(val) || 0)) / maxVal;
      }

      // X-axis labels, spaced by the room available (about one per 110px)
      var step = Math.max(1, Math.ceil(rows.length / Math.max(2, Math.floor(plotW / 110))));
      for (var i = 0; i < rows.length; i += step) {
        var xLabel = document.createElementNS(ns, 'text');
        xLabel.setAttribute('x', xFor(i));
        xLabel.setAttribute('y', height - 10);
        xLabel.setAttribute('text-anchor', 'middle');
        xLabel.setAttribute('class', 'otm-chart-label');
        xLabel.textContent = dayLabel(rows[i][0]);
        svg.appendChild(xLabel);
      }

      // Draw area + smooth line for each series
      for (var sIdx = 0; sIdx < numSeries; sIdx++) {
        var seriesColor = colors[sIdx] || '#4f46e5';
        var pts = rows.map(function (r, idx) {
          return { x: xFor(idx), y: yFor(r[sIdx + 1]) };
        });

        var linePath = pts.map(function (p, idx) {
          return (idx === 0 ? 'M' : 'L') + p.x.toFixed(1) + ',' + p.y.toFixed(1);
        }).join(' ');

        var areaPath = linePath +
          ' L' + pts[pts.length - 1].x.toFixed(1) + ',' + (pad.top + plotH).toFixed(1) +
          ' L' + pts[0].x.toFixed(1) + ',' + (pad.top + plotH).toFixed(1) + ' Z';

        var areaEl = document.createElementNS(ns, 'path');
        areaEl.setAttribute('d', areaPath);
        areaEl.setAttribute('fill', 'url(#otm-grad-' + sIdx + ')');
        svg.appendChild(areaEl);

        var pathEl = document.createElementNS(ns, 'path');
        pathEl.setAttribute('d', linePath);
        pathEl.setAttribute('fill', 'none');
        pathEl.setAttribute('stroke', seriesColor);
        pathEl.setAttribute('stroke-width', '2.5');
        pathEl.setAttribute('stroke-linecap', 'round');
        pathEl.setAttribute('stroke-linejoin', 'round');
        svg.appendChild(pathEl);

        // Dots only while they can be told apart (long ranges would turn into a smear)
        pts.forEach(function (p, idx) {
          if (rows.length > 45) {
            return;
          }
          var dot = document.createElementNS(ns, 'circle');
          dot.setAttribute('cx', p.x.toFixed(1));
          dot.setAttribute('cy', p.y.toFixed(1));
          dot.setAttribute('r', '3.5');
          dot.setAttribute('fill', '#ffffff');
          dot.setAttribute('stroke', seriesColor);
          dot.setAttribute('stroke-width', '2');
          var title = document.createElementNS(ns, 'title');
          title.textContent = headers[sIdx + 1] + ' (' + dayLabel(rows[idx][0]) + '): ' + rows[idx][sIdx + 1];
          dot.appendChild(title);
          svg.appendChild(dot);
        });
      }

      container.innerHTML = '';
      container.appendChild(svg);
    }
  })();
})(window, document);



/* Called by Joomla
-------------------------------------------------- */
// Called by Joomla's media manager (image chosen for a designer position)
function jInsertFieldValue(image, editor) {
  'use strict';
  var doc = document.getElementById('otm-designer-frame').contentDocument;
  var position = doc.getElementById(editor);
  if (position) {
    var img = doc.createElement('img');
    img.setAttribute('src', Ottomo.sitePath + image);
    img.setAttribute('alt', '');
    position.insertAdjacentHTML('beforeend', Ottomo.sortableItem(img.outerHTML));
    Ottomo.newsletterModified = true;
    Ottomo.updateSourceEditor();
    Ottomo.editor.fitImages(position.lastElementChild); // a 1920px photo must not widen the email
  }
}

// Called by Joomla modals to close themselves
function jModalClose() {
  'use strict';
  if (window.SuperModal) {
    SuperModal.close();
  }
}

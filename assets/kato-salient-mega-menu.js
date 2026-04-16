(function () {
  function initMegaMenu(root) {
    var level2Items = root.querySelectorAll('.kato-mega-menu__level2-item');
    var previewLink = root.querySelector('.kato-mega-menu__preview-link');
    var previewImage = root.querySelector('.kato-mega-menu__preview-image, .kato-mega-menu__preview-image--placeholder');
    var previewContent = root.querySelector('.kato-mega-menu__preview-content');
    var previewHeading = root.querySelector('.kato-mega-menu__heading--preview');
    var previewDesc = root.querySelector('.kato-mega-menu__preview-desc');
    var previewCta = root.querySelector('.kato-mega-menu__preview-cta');
    var level3Col = root.querySelector('.kato-mega-menu__col--level3');
    var level3Target = root.querySelector('.kato-mega-menu__level3-target');
    var templates = root.querySelector('.kato-mega-menu__templates');
    var panel = root.querySelector('.kato-mega-menu__panel');

    if (!level2Items.length || !previewLink || !level3Target || !templates || !panel) {
      return;
    }

    var defaultImage = panel.getAttribute('data-default-preview-image') || '';
    var defaultTitle = panel.getAttribute('data-default-preview-title') || '';
    var defaultLink = panel.getAttribute('data-default-preview-link') || '#';

    function ensureImageNode(src, altText) {
      if (src) {
        if (!previewImage || previewImage.tagName !== 'IMG') {
          var img = document.createElement('img');
          img.className = 'kato-mega-menu__preview-image';
          if (previewImage && previewImage.parentNode) {
            previewImage.parentNode.replaceChild(img, previewImage);
          }
          previewImage = img;
        }
        previewImage.setAttribute('src', src);
        previewImage.setAttribute('alt', altText || '');
        return;
      }

      if (!previewImage || previewImage.tagName === 'IMG') {
        var placeholder = document.createElement('span');
        placeholder.className = 'kato-mega-menu__preview-image kato-mega-menu__preview-image--placeholder';
        placeholder.setAttribute('aria-hidden', 'true');
        if (previewImage && previewImage.parentNode) {
          previewImage.parentNode.replaceChild(placeholder, previewImage);
        }
        previewImage = placeholder;
      }
    }

    function resetToLevel1() {
      level2Items.forEach(function (node) {
        node.classList.remove('is-active');
      });

      previewLink.setAttribute('href', defaultLink);
      ensureImageNode(defaultImage, defaultTitle);

      if (previewContent) {
        previewContent.classList.add('is-hidden');
      }
      if (previewHeading) {
        previewHeading.textContent = '';
      }
      if (previewDesc) {
        previewDesc.textContent = '';
        previewDesc.classList.add('is-hidden');
      }
      if (previewCta) {
        previewCta.textContent = '';
      }
      if (level3Target) {
        level3Target.innerHTML = '';
      }
      if (level3Col) {
        level3Col.classList.add('is-empty');
      }
    }

    function setActive(item) {
      level2Items.forEach(function (node) {
        node.classList.toggle('is-active', node === item);
      });

      var image = item.getAttribute('data-preview-image') || '';
      var title = item.getAttribute('data-preview-title') || '';
      var desc = item.getAttribute('data-preview-desc') || '';
      var link = item.getAttribute('data-preview-link') || '#';
      var cta = item.getAttribute('data-preview-cta') || 'Read more';
      var key = item.getAttribute('data-kato-key');

      previewLink.setAttribute('href', link);
      ensureImageNode(image, title);

      if (previewContent) {
        previewContent.classList.remove('is-hidden');
      }
      if (previewHeading) {
        previewHeading.textContent = title;
      }
      if (previewDesc) {
        previewDesc.textContent = desc || '';
        previewDesc.classList.toggle('is-hidden', !desc);
      }
      if (previewCta) {
        previewCta.innerHTML = '';
        previewCta.append(document.createTextNode(cta + ' '));
        var arrow = document.createElement('span');
        arrow.setAttribute('aria-hidden', 'true');
        arrow.textContent = '→';
        previewCta.appendChild(arrow);
      }

      var template = templates.querySelector('template[data-kato-key="' + key + '"]');
      level3Target.innerHTML = template ? template.innerHTML : '';
      if (level3Col) {
        level3Col.classList.toggle('is-empty', !template || !level3Target.innerHTML.trim());
      }
    }

    level2Items.forEach(function (item) {
      item.addEventListener('mouseenter', function () { setActive(item); });
      item.addEventListener('focusin', function () { setActive(item); });
      item.addEventListener('mouseleave', function (event) {
        var next = event.relatedTarget;
        if (!next) {
          resetToLevel1();
          return;
        }
        if (item.contains(next)) {
          return;
        }
        if (level3Col && level3Col.contains(next)) {
          return;
        }
        if (next.closest && next.closest('.kato-mega-menu__level2-item')) {
          return;
        }
        resetToLevel1();
      });
    });

    panel.addEventListener('mouseleave', function () {
      resetToLevel1();
    });

    resetToLevel1();
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.kato-mega-menu').forEach(initMegaMenu);
  });
})();

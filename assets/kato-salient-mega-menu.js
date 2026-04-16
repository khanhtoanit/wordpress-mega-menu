(function () {
  function initMegaMenu(root) {
    var level2Items = root.querySelectorAll('.kato-mega-menu__level2-item');
    var previewLink = root.querySelector('.kato-mega-menu__preview-link');
    var previewImage = root.querySelector('.kato-mega-menu__preview-image');
    var previewHeading = root.querySelector('.kato-mega-menu__heading--preview');
    var previewDesc = root.querySelector('.kato-mega-menu__preview-desc');
    var previewCta = root.querySelector('.kato-mega-menu__preview-cta');
    var level3Target = root.querySelector('.kato-mega-menu__level3-target');
    var templates = root.querySelector('.kato-mega-menu__templates');

    if (!level2Items.length || !previewLink || !level3Target || !templates) {
      return;
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
      previewHeading.textContent = title;
      if (previewDesc) {
        previewDesc.textContent = desc;
        previewDesc.style.display = '';
      } else if (previewDesc) {
        previewDesc.textContent = '';
        previewDesc.style.display = 'none';
      }
      if (previewCta) {
        previewCta.innerHTML = '';
        previewCta.append(document.createTextNode(cta + ' '));
        var arrow = document.createElement('span');
        arrow.setAttribute('aria-hidden', 'true');
        arrow.textContent = '→';
        previewCta.appendChild(arrow);
      }

      if (previewImage) {
        if (image) {
          if (previewImage.tagName === 'IMG') {
            previewImage.setAttribute('src', image);
            previewImage.setAttribute('alt', title);
          } else {
            var img = document.createElement('img');
            img.className = 'kato-mega-menu__preview-image';
            img.src = image;
            img.alt = title;
            previewImage.replaceWith(img);
            previewImage = img;
          }
        } else if (previewImage.tagName === 'IMG') {
          var placeholder = document.createElement('span');
          placeholder.className = 'kato-mega-menu__preview-image kato-mega-menu__preview-image--placeholder';
          placeholder.setAttribute('aria-hidden', 'true');
          previewImage.replaceWith(placeholder);
          previewImage = placeholder;
        }
      }

      var template = templates.querySelector('template[data-kato-key="' + key + '"]');
      if (template) {
        level3Target.innerHTML = template.innerHTML;
      }
    }

    level2Items.forEach(function (item) {
      item.addEventListener('mouseenter', function () { setActive(item); });
      item.addEventListener('focusin', function () { setActive(item); });
    });

    setActive(level2Items[0]);
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.kato-mega-menu').forEach(initMegaMenu);
  });
})();

/**
 * @file
 * Tab panel app.
 */
((Drupal) => {
  function openTab(name) {
    const contents = document.getElementsByClassName(
      'webprofiler__tabs__content',
    );
    for (let i = 0; i < contents.length; i++) {
      contents[i].style.display = 'none';
    }

    const labels = document.getElementsByClassName('webprofiler__tabs__label');
    for (let i = 0; i < labels.length; i++) {
      labels[i].className = labels[i].className.replace(' active', '');
    }

    if (document.getElementById(name)) {
      document.getElementById(name).style.display = 'block';
      document.querySelector(`[data-tab-id="${name}"]`).className += ' active';
    }
  }

  Drupal.behaviors.webprofiler_tab = {
    attach(context) {
      context
        .querySelectorAll('.webprofiler__tabs__label')
        .forEach((element) => {
          element.addEventListener('click', (event) => {
            openTab(event.currentTarget.dataset.tabId);
          });
        });

      openTab('js-webprofiler__tab-0');
    },
  };
})(Drupal);

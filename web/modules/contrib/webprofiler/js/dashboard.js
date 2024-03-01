/**
 * @file
 * Main dashboard script.
 */
((Drupal, drupalSettings) => {
  Drupal.behaviors.webprofiler_dashboard = {
    attach(context) {
      // Automatically open the panel if the URL contains the query parameter.
      once('opener', '.webprofiler__collectors', context).forEach(() => {
        const { path } = drupalSettings;

        if (path.currentQuery && 'panel' in path.currentQuery) {
          const { panel } = path.currentQuery;
          const panelLink = document.querySelector(
            `.webprofiler__collectors [data-collector-name='${panel}']`,
          );
          panelLink.click();
          panelLink.parentNode.className += ' active';
        }
      });
    },
  };
})(Drupal, drupalSettings);

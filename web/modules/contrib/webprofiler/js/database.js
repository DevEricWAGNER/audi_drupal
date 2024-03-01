/**
 * @file
 * Database panel app.
 */
((Drupal) => {
  const queryTpl = _.template(`
    <table class="webprofiler__table responsive-enabled" data-striping="1">
        <thead>
            <tr>
                <th>Time</th>
                <th>Caller</th>
                <th>Database</th>
                <th>Target</th>
            </tr>
        </thead>
        <tbody>
            <tr class="odd">
                <td class="webprofiler__key"><% print(time); %> ms</td>
                <td class="webprofiler__key"><% print(caller); %></td>
                <td class="webprofiler__key"><% print(db); %></td>
                <td class="webprofiler__key"><% print(target); %></td>
            </tr>
        </tbody>
    </table>

    <div class="wp-executable-actions">
      <% if (hasArgs == 1) {%><a class="wp-executable-toggle">Swap placeholders</a><%}%>
      <a class="wp-query-copy">Copy query</a>
      <% if (type == "SELECT") {%><a href="<% print(explainPath); %>" class="use-ajax wp-query-explain">Explain</a><%}%>
    </div>
    <div class="js--explain-target-<% print(qid); %>"></div>
  `);

  Drupal.behaviors.webprofiler_database = {
    attach() {
      // eslint-disable-next-line no-undef
      hljs.configure({
        ignoreUnescapedHTML: true,
      });

      once('db', '.wp-db-query').forEach((element) => {
        const result = queryTpl({
          time: element.dataset.wpTime,
          caller: element.dataset.wpClass,
          db: element.dataset.wpDb,
          target: element.dataset.wpTarget,
          hasArgs: element.dataset.wpHasArgs,
          type: element.dataset.wpType,
          qid: element.dataset.wpQid,
          explainPath: element.dataset.wpExplainPath,
        });

        element.innerHTML += result;

        element.querySelectorAll('code').forEach((code) => {
          // eslint-disable-next-line no-undef
          hljs.highlightElement(code);
        });

        // Swap placeholders.
        if (element.dataset.wpHasArgs === '1') {
          element
            .querySelector('.wp-executable-toggle')
            .addEventListener('click', () => {
              element
                .querySelector('.wp-query-placeholder')
                .classList.toggle('is-hidden');
              element
                .querySelector('.wp-query-executable')
                .classList.toggle('is-hidden');
            });
        }

        // Copy to clipboard.
        if (navigator.clipboard && window.isSecureContext) {
          element
            .querySelector('.wp-query-copy')
            .addEventListener('click', () => {
              const query = element.querySelector(
                '.wp-query-executable',
              ).innerText;
              navigator.clipboard.writeText(query);
            });
        } else {
          element.querySelector('.wp-query-copy').classList.toggle('is-hidden');
        }
      });
    },
  };
})(Drupal);

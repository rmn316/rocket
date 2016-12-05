export function config ($logProvider, $httpProvider) {
  'ngInject';
  // Enable log
  $logProvider.debugEnabled(true);

    $httpProvider.defaults.headers.post["Content-Type"] = "application/form-data; charset=UTF-8;";
}

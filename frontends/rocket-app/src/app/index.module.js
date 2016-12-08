/* global malarkey:false, moment:false */

import {config} from './index.config';
import {routerConfig} from './index.route';
import {runBlock} from './index.run';
import {MainController} from './main/controllers/main.controller';

angular.module(
    'frontendApp',
    ['ngAnimate', 'ngTouch', 'ngSanitize', 'ngAria', 'ui.router', 'ui.bootstrap', 'ui.bootstrap.showErrors', 'xeditable']
)
    .config(config)
    .config(routerConfig)
    .run(runBlock)
    .controller('MainController', MainController);

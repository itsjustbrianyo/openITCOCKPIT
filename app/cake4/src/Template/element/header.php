<?php
// Copyright (C) <2015>  <it-novum GmbH>
//
// This file is dual licensed
//
// 1.
//	This program is free software: you can redistribute it and/or modify
//	it under the terms of the GNU General Public License as published by
//	the Free Software Foundation, version 3 of the License.
//
//	This program is distributed in the hope that it will be useful,
//	but WITHOUT ANY WARRANTY; without even the implied warranty of
//	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//	GNU General Public License for more details.
//
//	You should have received a copy of the GNU General Public License
//	along with this program.  If not, see <http://www.gnu.org/licenses/>.
//

// 2.
//	If you purchased an openITCOCKPIT Enterprise Edition you can use this file
//	under the terms of the openITCOCKPIT Enterprise Edition license agreement.
//	License agreement and license key will be shipped with the order
//	confirmation.

?>
<!-- HEADER START -->
<header id="header" class="page-header" role="banner">
    <div class="hidden-md-down dropdown-icon-menu position-relative">
        <a href="#" class="header-btn btn js-waves-off" data-action="toggle"
           data-class="nav-function-hidden" title="Hide Navigation">
            <i class="ni ni-menu"></i>
        </a>
        <ul>
            <li>
                <a href="#" class="btn js-waves-off" data-action="toggle" data-class="nav-function-minify"
                   title="Minify Navigation">
                    <i class="ni ni-minify-nav"></i>
                </a>
            </li>
            <li>
                <a href="#" class="btn js-waves-off" data-action="toggle" data-class="nav-function-fixed"
                   title="Lock Navigation">
                    <i class="ni ni-lock-nav"></i>
                </a>
            </li>
        </ul>
    </div>
    <div class="hidden-lg-up">
        <a href="#" class="header-btn btn press-scale-down" data-action="toggle" data-class="mobile-nav-on">
            <i class="ni ni-menu"></i>
        </a>
    </div>
    <div class="search">
        <form class="app-forms hidden-xs-down row padding-top-10" role="search" action="page_search.html" autocomplete="off">
            <div class="input-group">
                <select
                    data-placeholder="<?php echo __('Choose Type'); ?>"
                    class="form-control custom-select no-border"
                    style="max-width: 100px;"
                    >
                    <option value="host">Host</option>
                    <option value="host">Service</option>
                    <option value="host">UUID</option>
                </select>
                <input type="text" id="" placeholder="Search for anything" class="form-control no-border"
                       tabindex="1">
            </div>

    <div class="pull-left" top-search="">
        <!-- Content get loaded by AngularJS Directive -->
    </div>





        </form>
    </div>

    <div class="ml-auto d-flex">
        <div class="header-icon">
                <span id="global_ajax_loader">
                    <div class="spinner-border spinner-border-sm" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </span>
        </div>
        <div class="header-icon">
            <version-check></version-check>
        </div>
        <div class="header-icon">
            <?php if ($showstatsinmenu): ?>
                <menustats></menustats>
            <?php endif; ?>
        </div>
        <div>
            <system-health></system-health>
        </div>
        <div class="header-icon">
            <server-time></server-time>
        </div>
        <div>
            <?php if ($exportRunningHeaderInfo === false): ?>
                <a ui-sref="ExportsIndex" sudo-server-connect=""
                   data-original-title="<?php echo __('Refresh monitoring configuration'); ?>"
                   data-placement="left" rel="tooltip" data-container="body" class="header-icon">
                    <i class="fa fa-retweet"></i>
                </a>
            <?php else: ?>
                <a ui-sref="ExportsIndex" export-status=""
                   data-original-title="<?php echo __('Refresh monitoring configuration'); ?>"
                   data-placement="left" rel="tooltip" data-container="body" class="header-icon">
                    <i class="fa fa-retweet" ng-hide="exportRunning"></i>
                    <i class="fa fa-refresh fa-spin txt-color-red" ng-show="exportRunning"></i>
                </a>
            <?php endif; ?>
        </div>
        <div>
            <a href="/users/logout" data-original-title="<?php echo __('Sign out'); ?>"
               data-placement="left"
               rel="tooltip" data-container="body" class="header-icon">
                <i class="fa fa-sign-out-alt"></i>
            </a>
        </div>
        <push-notifications></push-notifications>
    </div>

</header>

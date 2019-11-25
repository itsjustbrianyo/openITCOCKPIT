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

declare(strict_types=1);

namespace App\Controller;

use Acl\Model\Table\AcosTable;
use Acl\Model\Table\ArosTable;
use App\Model\Table\ContainersTable;
use App\Model\Table\UsersTable;
use Authentication\Controller\Component\AuthenticationComponent;
use Authorization\Identity;
use Cake\Cache\Cache;
use Cake\Controller\Controller;
use Cake\Event\EventInterface;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;

/**
 * Class AppController
 * @package App\Controller
 * @property AuthenticationComponent $Authentication
 */
class AppController extends Controller {

    /**
     * @var bool
     */
    protected $hasRootPrivileges = false;

    /**
     * @var array
     */
    public $MY_RIGHTS = [];

    /**
     * @var array
     */
    public $MY_RIGHTS_LEVEL = [];

    /**
     * @var array
     */
    protected $PERMISSIONS = [];

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('Security');`
     *
     * @return void
     * @throws \Exception
     */
    public function initialize(): void {
        parent::initialize();

        $this->loadComponent('RequestHandler');
        $this->loadComponent('Flash');

        // Docs: https://book.cakephp.org/authentication/1/en/index.html
        $this->loadComponent('Authentication.Authentication', [
            'logoutRedirect' => '/users/login'  // Default is false
        ]);

        /*
         * Enable the following component for recommended CakePHP security settings.
         * see https://book.cakephp.org/4/en/controllers/components/security.html
         */
        //$this->loadComponent('Security');
    }

    /**
     * @return bool
     */
    protected function isHtmlRequest(): bool {
        return $this->request->getParam('_ext') === 'html';
    }

    /**
     * @return bool
     */
    protected function isJsonRequest(): bool {
        return $this->request->getParam('_ext') === 'json';
    }

    /**
     * @return bool
     */
    protected function isXmlRequest() {
        return $this->request->getParam('_ext') === 'xml';
    }

    /**
     * @return bool
     */
    protected function isPdfRequest(): bool {
        return $this->request->getParam('_ext') === 'pdf';
    }

    protected function isApiRequest() {
        if ($this->isJsonRequest() || $this->isXmlRequest()) {
            return true;
        }

        return false;
    }

    public function beforeFilter(EventInterface $event) {
        parent::beforeFilter($event);

        $user = $this->Authentication->getIdentity();
        $userId = $user->get('id');

        if ($userId > 0) {
            //User is logged in
            $cacheKey = 'userPermissions_' . $userId;

            if (Cache::read($cacheKey, 'permissions') === null) {
                $permissions = $this->getUserPermissions($user);

                Cache::write($cacheKey, $permissions, 'permissions');
            }

            $permissions = Cache::read($cacheKey, 'permissions');

            $this->MY_RIGHTS = $permissions['MY_RIGHTS'];
            $this->MY_RIGHTS_LEVEL = $permissions['MY_RIGHTS_LEVEL'];
            $this->PERMISSIONS = $permissions['PERMISSIONS'];
            $this->hasRootPrivileges = $permissions['hasRootPrivileges'];
        }
    }

    /**
     * Add CSRF token to all .json requests
     */
    public function beforeRender(EventInterface $event) {
        if ($this->isJsonRequest()) {
            $this->set('_csrfToken', $this->request->getAttribute('csrfToken'));
            $serialize = $this->viewBuilder()->getOption('serialize');
            if ($serialize === null) {
                $serialize = [];
            }
            $serialize[] = '_csrfToken';
            //Add Paginator info to json response
            $paging = $this->viewBuilder()->getVar('paging');
            if ($paging !== null) {
                $serialize[] = 'paging';
            }
            $this->viewBuilder()->setOption('serialize', $serialize);
        }
    }

    /**
     * @param Identity $identity
     * @return array
     */
    private function getUserPermissions(Identity $identity) {
        /** @var UsersTable $UsersTable */
        $UsersTable = TableRegistry::getTableLocator()->get('Users');

        $userId = $identity->get('id');
        $usergroupId = $identity->get('usergroup_id');

        /** @var ContainersTable $ContainersTable */
        $ContainersTable = TableRegistry::getTableLocator()->get('Containers');

        $user = $UsersTable->getUserById($userId);

        //unify the usercontainerrole permissions
        $usercontainerrolePermissions = [];
        foreach ($user['usercontainerroles'] as $usercontainerrole) {
            foreach ($usercontainerrole['containers'] as $usercontainerroleContainer) {
                $currentId = $usercontainerroleContainer['id'];
                if (isset($usercontainerrolePermissions[$currentId])) {
                    //highest usercontainerrole permission wins
                    if ($usercontainerrolePermissions[$currentId]['_joinData']['permission_level'] < $usercontainerroleContainer['_joinData']['permission_level']) {
                        $usercontainerrolePermissions[$currentId] = $usercontainerroleContainer;
                        continue;
                    }
                } else {
                    $usercontainerrolePermissions[$currentId] = $usercontainerroleContainer;
                }
            }
        }

        //merge permissions from usercontainerrole with the user container permissions
        //User container permissions override permissions from the role
        $containerPermissions = [];
        $containerPermissionsUser = [];
        foreach ($usercontainerrolePermissions as $usercontainerrolePermission) {
            $containerPermissions[$usercontainerrolePermission['id']] = $usercontainerrolePermission;
        }
        foreach ($user['containers'] as $container) {
            $containerPermissionsUser[$container['id']] = $container;
        }

        $containerPermissions = $containerPermissionsUser + $containerPermissions;

        $MY_RIGHTS = [ROOT_CONTAINER];
        $MY_RIGHTS_LEVEL = [ROOT_CONTAINER => READ_RIGHT];

        foreach ($containerPermissions as $container) {
            $container = $container['_joinData'];
            $MY_RIGHTS[] = (int)$container['container_id'];
            $MY_RIGHTS_LEVEL[(int)$container['container_id']] = $container['permission_level'];

            if ((int)$container['container_id'] === ROOT_CONTAINER) {
                $MY_RIGHTS_LEVEL[ROOT_CONTAINER] = WRITE_RIGHT;
                $this->hasRootPrivileges = true;
            }

            foreach ($ContainersTable->getChildren($container['container_id']) as $childContainer) {
                $MY_RIGHTS[] = (int)$childContainer['id'];
                $MY_RIGHTS_LEVEL[(int)$childContainer['id']] = $container['permission_level'];
            }
        }

        /** @var ArosTable $ArosTable */
        $ArosTable = TableRegistry::getTableLocator()->get('Acl.Aros');
        /** @var AcosTable $AcosTable */
        $AcosTable = TableRegistry::getTableLocator()->get('Acl.Acos');

        $AcosAros = $ArosTable->find()
            ->where([
                'Aros.foreign_key' => $usergroupId
            ])
            ->contain([
                'Acos'
            ])
            ->disableHydration()
            ->first();

        $acos = $AcosAros['acos'];


        $acoIdsOfUsergroup = Hash::combine($acos, '{n}.id', '{n}.id');
        unset($acos, $AcosAros);

        $acos = $AcosTable->find('threaded')
            ->disableHydration()
            ->all();

        $acos = $acos->toArray();

        $permissions = [];
        foreach ($acos as $usergroupAcos) {
            foreach ($usergroupAcos['children'] as $controllerAcos) {
                $controllerName = strtolower($controllerAcos['alias']);
                if (!strpos($controllerName, 'module')) {
                    //Core
                    foreach ($controllerAcos['children'] as $actionAcos) {
                        //Check if the user group is allowd for $actionAcos action
                        if (!isset($acoIdsOfUsergroup[$actionAcos['id']])) {
                            continue;
                        }
                        $actionName = strtolower($actionAcos['alias']);
                        $permissions[$controllerName][$actionName] = $actionName;
                    }
                } else {
                    //Plugin / Module
                    $pluginName = Inflector::underscore($controllerName);
                    $pluginAcos = $controllerAcos;
                    foreach ($pluginAcos['children'] as $controllerAcos) {

                        $controllerName = strtolower($controllerAcos['alias']);
                        foreach ($controllerAcos['children'] as $actionAcos) {
                            //Check if the user group is allowd for $actionAcos action
                            if (!isset($acoIdsOfUsergroup[$actionAcos['id']])) {
                                continue;
                            }
                            $actionName = strtolower($actionAcos['alias']);
                            $permissions[$pluginName][$controllerName][$actionName] = $actionName;
                        }
                    }
                }
            }
        }

        $userPermissions = [
            'MY_RIGHTS'         => array_unique($MY_RIGHTS),
            'MY_RIGHTS_LEVEL'   => $MY_RIGHTS_LEVEL,
            'PERMISSIONS'       => $permissions,
            'hasRootPrivileges' => $this->hasRootPrivileges
        ];

        return $userPermissions;
    }
}
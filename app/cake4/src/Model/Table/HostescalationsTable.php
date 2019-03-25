<?php

namespace App\Model\Table;

use App\Lib\Traits\Cake2ResultTableTrait;
use App\Lib\Traits\PaginationAndScrollIndexTrait;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use itnovum\openITCOCKPIT\Filter\HostescalationsFilter;

/**
 * Hostescalations Model
 *
 * @property \App\Model\Table\ContainersTable|\Cake\ORM\Association\BelongsTo $Containers
 * @property \App\Model\Table\HostescalationTable|\Cake\ORM\Association\HasMany $Hosts
 * @property \App\Model\Table\HostescalationTable|\Cake\ORM\Association\HasMany $Hostgroups
 *
 * @method \App\Model\Entity\Hostescalation get($primaryKey, $options = [])
 * @method \App\Model\Entity\Hostescalation newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Hostescalation[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Hostescalation|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Hostescalation|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Hostescalation patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Hostescalation[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Hostescalation findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class HostescalationsTable extends Table {

    use Cake2ResultTableTrait;
    use PaginationAndScrollIndexTrait;


    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config) {
        parent::initialize($config);
        $this->addBehavior('Timestamp');

        $this->setTable('hostescalations');
        $this->setPrimaryKey('id');

        $this->belongsTo('Containers', [
            'foreignKey' => 'container_id',
            'joinType'   => 'INNER'
        ]);
        $this->belongsTo('Timeperiods', [
            'foreignKey' => 'timeperiod_id',
            'joinType'   => 'INNER'
        ]);
        $this->belongsToMany('Contacts', [
            'joinTable'    => 'contacts_to_hostescalations',
            'saveStrategy' => 'replace'
        ]);
        $this->belongsToMany('Contactgroups', [
            'joinTable'    => 'contactgroups_to_hostescalations',
            'saveStrategy' => 'replace'
        ]);

        $this->belongsToMany('Hosts', [
            'through'      => 'HostescalationsHostMemberships',
            'saveStrategy' => 'replace'
        ]);
        $this->belongsToMany('Hostgroups', [
            'through'      => 'HostescalationsHostgroupMemberships',
            'saveStrategy' => 'replace'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator) {
        $validator
            ->integer('id')
            ->allowEmptyString('id', 'create');

        $validator
            ->scalar('uuid')
            ->maxLength('uuid', 37)
            ->requirePresence('uuid', 'create')
            ->allowEmptyString('uuid', false)
            ->add('uuid', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->integer('container_id')
            ->greaterThan('container_id', 0)
            ->requirePresence('container_id')
            ->allowEmptyString('container_id', false);

        $validator
            ->add('contacts', 'custom', [
                'rule'    => [$this, 'atLeastOne'],
                'message' => __('You must specify at least one contact or contact group.')
            ]);

        $validator
            ->add('contactgroups', 'custom', [
                'rule'    => [$this, 'atLeastOne'],
                'message' => __('You must specify at least one contact or contact group.')
            ]);

        $validator
            ->requirePresence('hosts', true, __('You have to choose at least one host.'))
            ->allowEmptyString('hosts', false)
            ->multipleOptions('hosts', [
                'min' => 1
            ], __('You have to choose at least one host.'));

        $validator
            ->integer('timeperiod_id')
            ->greaterThan('timeperiod_id', 0)
            ->requirePresence('timeperiod_id')
            ->allowEmptyString('timeperiod_id', false);

        $validator
            ->integer('first_notification')
            ->greaterThan('first_notification', 0)
            ->lessThanField('first_notification', 'last_notification', __('The first notification must be before the last notification.'),
                function ($context) {
                    return !($context['data']['last_notification'] === 0);
                })
            ->requirePresence('first_notification')
            ->allowEmptyString('first_notification', false);

        $validator
            ->integer('last_notification')
            ->greaterThanOrEqual('last_notification', 0)
            ->greaterThanField('last_notification', 'first_notification', __('The first notification must be before the last notification.'),
                function ($context) {
                    return !($context['data']['last_notification'] === 0);
                })
            ->requirePresence('last_notification')
            ->allowEmptyString('last_notification', false);

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules) {
        $rules->add($rules->isUnique(['uuid']));

        return $rules;
    }

    /**
     * @param mixed $value
     * @param array $context
     * @return bool
     *
     * Custom validation rule for contacts and or contact groups
     */
    public function atLeastOne($value, $context) {
        return !empty($context['data']['contacts']['_ids']) || !empty($context['data']['contactgroups']['_ids']);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function existsById($id) {
        return $this->exists(['Hostescalations.id' => $id]);
    }

    /**
     * @param HostescalationsFilter $HostescalationsFilter
     * @param null $PaginateOMat
     * @param array $MY_RIGHTS
     * @return array
     */
    public function getHostescalationsIndex(HostescalationsFilter $HostescalationsFilter, $PaginateOMat = null, $MY_RIGHTS = []) {
        $query = $this->find('all')
            ->contain([
                'Contacts'      => function ($q) {
                    return $q->enableAutoFields(false)
                        ->select([
                            'Contacts.id',
                            'Contacts.name'
                        ]);
                },
                'Contactgroups' => [
                    'Containers' => function ($q) {
                        return $q->enableAutoFields(false)
                            ->select([
                                'Contactgroups.id',
                                'Containers.name'
                            ]);
                    },
                ],
                'Timeperiods'   => function ($q) {
                    return $q->enableAutoFields(false)
                        ->select([
                            'Timeperiods.id',
                            'Timeperiods.name'
                        ]);
                },
                'Hosts'         => function ($q) {
                    return $q->enableAutoFields(false)
                        ->select([
                            'Hosts.id',
                            'Hosts.name',
                            'Hosts.disabled'
                        ]);
                },
                'Hostgroups'    => [
                    'Containers' => function ($q) {
                        return $q->enableAutoFields(false)
                            ->select([
                                'Hostgroups.id',
                                'Containers.name'
                            ]);
                    },
                ]
            ])
            ->disableHydration();
        $indexFilter = $HostescalationsFilter->indexFilter();
        if (array_key_exists('Hostescalations.escalate_on_recovery', $indexFilter) ||
            array_key_exists('Hostescalations.escalate_on_down', $indexFilter) ||
            array_key_exists('Hostescalations.escalate_on_unreachable', $indexFilter)
        ) {
            $query->where(function ($exp, Query $q) use ($indexFilter) {
                //debug($exp);
                //$defaultExp = $exp->and_($indexFilter);
                /*
                $escalateConditions = $exp->add([
                    'Hostescalations.escalate_on_recovery' => $indexFilter['Hostescalations.escalate_on_recovery']
                ])
                    ->or_([])
                    ->gt('Hostescalations.escalate_on_recovery', 0)
                    ->gt('Hostescalations.escalate_on_down', 0)
                    ->gt('Hostescalations.escalate_on_unreachable', 0);
                $exp->add($escalateConditions);
                */
                $escalateOnConditions = [];
                if(array_key_exists('Hostescalations.escalate_on_recovery', $indexFilter)){
                    $escalateOnConditions[] = $exp->or_([
                        'Hostescalations.escalate_on_recovery' => $indexFilter['Hostescalations.escalate_on_recovery']
                    ]);
                    unset($indexFilter['Hostescalations.escalate_on_recovery']);
                }
                if(array_key_exists('Hostescalations.escalate_on_down', $indexFilter)){
                    $escalateOnConditions[] = $exp->or_([
                        'Hostescalations.escalate_on_down' => $indexFilter['Hostescalations.escalate_on_down']
                    ]);
                    unset($indexFilter['Hostescalations.escalate_on_down']);

                }
                if(array_key_exists('Hostescalations.escalate_on_unreachable', $indexFilter)){
                    $escalateOnConditions[] = $exp->or_([
                        'Hostescalations.escalate_on_unreachable' => $indexFilter['Hostescalations.escalate_on_unreachable']
                    ]);
                    unset($indexFilter['Hostescalations.escalate_on_unreachable']);

                }

                $escalateOrConditions = $exp->or_($escalateOnConditions);
                $escalateAndConditions = $exp->and_([])
                    ->eq('Hostescalations.escalate_on_recovery', 0)
                    ->eq('Hostescalations.escalate_on_down', 0)
                    ->eq('Hostescalations.escalate_on_unreachable', 0);

                $escalateAndConditionsAll = $exp->or_([
                    $escalateAndConditions,
                    $escalateOrConditions
                ]);

                $exp->add($escalateAndConditionsAll);
                return $exp->and_([
                    $indexFilter,
                    $escalateAndConditionsAll
                ]);

            });
        } else {
            $query->where($indexFilter);
        }

        $query->innerJoinWith('Containers', function ($q) use ($MY_RIGHTS) {
            if (!empty($MY_RIGHTS)) {
                return $q->where(['Hostescalations.container_id IN' => $MY_RIGHTS]);
            }
            return $q;
        });

        $query->order($HostescalationsFilter->getOrderForPaginator('Hostescalations.first_notification', 'asc'));

        if ($PaginateOMat === null) {
            //Just execute query
            $result = $this->formatResultAsCake2($query->toArray(), false);
        } else {
            if ($PaginateOMat->useScroll()) {
                $result = $this->scroll($query, $PaginateOMat->getHandler(), false);
            } else {
                $result = $this->paginate($query, $PaginateOMat->getHandler(), false);
            }
        }

        return $result;
    }

    /**
     * @param array|int $hosts
     * @param array|int $excluded_hosts
     * @return array
     */
    public function parseHostMembershipData($hosts = [], $excluded_hosts = []) {
        $hostmembershipData = [];
        foreach ($hosts as $host) {
            $hostmembershipData[] = [
                'id'        => $host,
                '_joinData' => [
                    'excluded' => 0
                ]
            ];
        }
        foreach ($excluded_hosts as $excluded_host) {
            $hostmembershipData[] = [
                'id'        => $excluded_host,
                '_joinData' => [
                    'excluded' => 1
                ]
            ];
        }
        return $hostmembershipData;
    }

    /**
     * @param array $hostgroups
     * @param array $excluded_hostgroups
     * @return array
     */
    public function parseHostgroupMembershipData($hostgroups = [], $excluded_hostgroups = []) {
        $hostgroupmembershipData = [];
        foreach ($hostgroups as $hostgroup) {
            $hostgroupmembershipData[] = [
                'id'        => $hostgroup,
                '_joinData' => [
                    'excluded' => 0
                ]
            ];
        }
        foreach ($excluded_hostgroups as $excluded_hostgroup) {
            $hostgroupmembershipData[] = [
                'id'        => $excluded_hostgroup,
                '_joinData' => [
                    'excluded' => 1
                ]
            ];
        }
        return $hostgroupmembershipData;
    }
}

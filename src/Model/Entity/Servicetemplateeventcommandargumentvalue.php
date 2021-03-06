<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Servicetemplateeventcommandargumentvalue Entity
 *
 * @property int $id
 * @property int $commandargument_id
 * @property int $servicetemplate_id
 * @property string $value
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Commandargument $commandargument
 * @property \App\Model\Entity\Servicetemplate $servicetemplate
 */
class Servicetemplateeventcommandargumentvalue extends Entity {

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        'commandargument_id' => true,
        'servicetemplate_id' => true,
        'value'              => true,
        'created'            => true,
        'modified'           => true,
        'commandargument'    => true,
        'servicetemplate'    => true
    ];
}

<?php
declare(strict_types=1);

namespace Statusengine2Module\Model\Table;

use App\Lib\Interfaces\LogentriesTableInterface;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * NagiosLogentries Model
 *
 * @property \Statusengine2Module\Model\Table\LogentriesTable&\Cake\ORM\Association\BelongsTo $Logentries
 *
 * @method \Statusengine2Module\Model\Entity\Logentry get($primaryKey, $options = [])
 * @method \Statusengine2Module\Model\Entity\Logentry newEntity($data = null, array $options = [])
 * @method \Statusengine2Module\Model\Entity\Logentry[] newEntities(array $data, array $options = [])
 * @method \Statusengine2Module\Model\Entity\Logentry|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Statusengine2Module\Model\Entity\Logentry saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Statusengine2Module\Model\Entity\Logentry patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Statusengine2Module\Model\Entity\Logentry[] patchEntities($entities, array $data, array $options = [])
 * @method \Statusengine2Module\Model\Entity\Logentry findOrCreate($search, callable $callback = null, $options = [])
 */
class LogentriesTable extends Table implements LogentriesTableInterface {

    /*****************************************************/
    /*                         !!!                       */
    /*           If you add a method to this table       */
    /*   define it in the implemented interface first!   */
    /*                         !!!                       */
    /*****************************************************/

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void {
        parent::initialize($config);

        $this->setTable('nagios_logentries');
        $this->setDisplayField('logentry_id');
        $this->setPrimaryKey(['logentry_id']);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator {
        //Readonly table

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker {
        return $rules;
    }
}
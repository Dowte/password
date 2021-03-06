<?php
/**
 * Password - A command-line tool to help you manage your password
 *
 * @author  admin@dowte.com
 * @link    https://github.com/dowte/password
 * @license https://opensource.org/licenses/MIT
 */

namespace Dowte\Password\pass\db;

use Dowte\Password\pass\Password;

class ActiveRecord implements ActiveRecordInterface
{
    /**
     * @var BaseActiveRecordInterface
     */
    protected static $_db;

    /**
     * @var string
     */
    public static $className;

    /**
     * @var array
     */
    private $_attributeLabels = [];


    public function __construct()
    {
    }

    /**
     * @return QueryInterface
     */
    public static function find()
    {
        return Password::newObject(ActiveQuery::$className, get_called_class());
    }

    /**
     * @return integer
     */
    public function save()
    {
         return $this->getAR()->save();
    }

    public static function execSql($sql)
    {
        /* @var $AR ActiveRecordInterface */
        $AR = Password::newObject(ActiveRecord::$className, Password::$pd->db->config);
        return $AR->execSql($sql);
    }

    public function delete(array $conditions = [])
    {
        return $this->getAR()->delete($conditions);
    }

    public function __set($name, $value)
    {
        if (isset($this->attributeLabels()[$name])) {

            $this->_attributeLabels[$name] = $value;
        } else {
            throw new \Exception('Undefined property: ' . $name);
        }
    }

    public function __get($name)
    {
        if (isset($this->attributeLabels()[$name])) {
            return isset($this->_attributeLabels[$name]) ? $this->_attributeLabels[$name] : null;

        } else {
            throw new \Exception('Undefined property: ' . $name);
        }
    }

    /**
     * @return BaseActiveRecordInterface
     */
    protected function getAR()
    {
        return Password::newObject(ActiveRecord::$className, array_merge(Password::$pd->db->config, ['modelClass' => $this]));
    }

    public function attributeLabels(){}

    public function name(){}

    public function rules(){}
}

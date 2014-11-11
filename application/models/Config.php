<?php

namespace app\models;

use Yii;
use yii\caching\TagDependency;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "config".
 *
 * @property integer $id
 * @property integer $parent_id
 * @property string $name
 * @property string $key
 * @property string $value
 * @property integer $preload
 * @property string $path
 * @property Config[] $children
 * @property Config $parent
 */
class Config extends ActiveRecord
{
    private static $config;

    public function behaviors()
    {
        return [
            [
                'class' => \app\behaviors\TagDependency::className(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%config}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent_id', 'preload'], 'integer'],
            [['name', 'key'], 'required'],
            [['key'], 'string', 'max' => 50],
            [['name', 'value'], 'string', 'max' => 255],
            [['parent_id', 'key'], 'unique', 'targetAttribute' => ['parent_id', 'key']],
        ];
    }

    /**
     * Scenarios
     * @return array
     */
    public function scenarios()
    {
        return [
            'default' => ['parent_id', 'name', 'key', 'value', 'preload'],
            'search' => ['id', 'parent_id', 'name', 'key', 'value', 'preload'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'parent_id' => Yii::t('app', 'Parent ID'),
            'name' => Yii::t('app', 'Name'),
            'key' => Yii::t('app', 'Key'),
            'value' => Yii::t('app', 'Value'),
            'preload' => Yii::t('app', 'Preload'),
        ];
    }

    /**
     * Get preloaded config values.
     * @return array
     */
    public static function getConfig()
    {
        if (self::$config === null) {
            self::$config = Yii::$app->cache->get('PreloadedConfig');
            if (self::$config === false) {
                self::$config = [];
                $config = self::findAll(['preload' => 1]);
                foreach ($config as $item) {
                    self::$config[$item->path] = $item->value;
                }
                Yii::$app->cache->set(
                    'PreloadedConfig',
                    self::$config,
                    86400,
                    new TagDependency(
                        [
                            'tags' => [\app\behaviors\TagDependency::getCommonTag(Config::className())],
                        ]
                    )
                );
            }
        }
        return self::$config;
    }

    /**
     * Get config value by parentId and key
     * @param string $path
     * @param mixed $defaultValue
     * @return mixed|null
     */
    public static function getValue($path, $defaultValue = null)
    {
        $config = self::getConfig();
        if (isset($config[$path])) {
            return $config[$path];
        }
        $value = Yii::$app->cache->get('Config: ' . $path);
        if ($value === false) {
            $item = self::findOne(['path' => $path]);
            if ($item !== null) {
                Yii::$app->cache->set(
                    'Config: ' . $path,
                    $item->value,
                    86400,
                    new TagDependency(
                        [
                            'tags' => [\app\behaviors\TagDependency::getObjectTag($item, $item->id)],
                        ]
                    )
                );
                $value = $item->value;
            } else {
                $value = null;
            }
        }
        if ($value === null) {
            return $defaultValue;
        }
        self::$config[$path] = $value;
        return $value;
    }

    /**
     * Children relation.
     * @return \yii\db\ActiveQuery
     */
    public function getChildren()
    {
        return $this->hasMany(self::className(), ['parent_id' => 'id']);
    }

    /**
     * Parent relation.
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(self::className(), ['id' => 'parent_id']);
    }

    /**
     * Before save event.
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($this->parent_id == 0 || $this->parent === null) {
            $this->path = $this->key;
        } else {
            $this->path = $this->parent->path . '.' . $this->key;
        }
        return true;
    }

    /**
     * After save event.
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        foreach ($this->children as $child) {
            $child->save();
        }
    }

    /**
     * After delete event.
     */
    public function afterDelete()
    {
        parent::afterDelete();
        foreach ($this->children as $child) {
            $child->delete();
        }
    }
}

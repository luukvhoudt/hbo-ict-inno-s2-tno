<?php

namespace TNO\EssifLab\Tests\Stubs;

use TNO\EssifLab\Constants;
use TNO\EssifLab\Integrations\Contracts\Integration;
use TNO\EssifLab\ModelManagers\Contracts\BaseModelManager;
use TNO\EssifLab\Models\Contracts\Model;
use TNO\EssifLab\Tests\Stubs\Model as ConcreteModel;

class ModelManager extends BaseModelManager {
    const MODEL_MANAGER = 'ModelManager';

    private $isCalled = [];

    /**
     * @var Model[]
     */
    private $lastModel1ItsCalledWith = [];

    /**
     * @var Model[]
     */
    private $lastModel2ItsCalledWith = [];

    private $relations = [];

	function insert(Model $model): bool {
		return true;
	}

	function delete(Model $model): bool {
		return true;
	}

	function update(Model $model): bool {
		return true;
	}

	function select(Model $model, array $criteria = []): array {
		return [
			new ConcreteModel([
				Constants::TYPE_INSTANCE_IDENTIFIER_ATTR => 1,
				Constants::TYPE_INSTANCE_TITLE_ATTR => 'hello',
				Constants::TYPE_INSTANCE_DESCRIPTION_ATTR => 'world',
			])
		];
	}

	function insertRelation(Model $from, Model $to): bool {
        $this->callRenderer(self::MODEL_MANAGER, $from, $to);
        $this->relations[] = $to;
		return true;
	}

	function deleteRelation(Model $from, Model $to): bool {
        $this->callRenderer(self::MODEL_MANAGER, $from, $to);
        foreach ($this->relations as $key => $model){
            if($model == $to){
                unset($key, $this->relations);
            }
        }
		return true;
	}

	function deleteAllRelations(Model $model): bool {
		return true;
	}

	function selectAllRelations(Model $from, Model $to): array {
        return !empty($this->relations) ? $this->relations :
            [
                new ConcreteModel([
                    Constants::TYPE_INSTANCE_IDENTIFIER_ATTR => 1,
                    Constants::TYPE_INSTANCE_TITLE_ATTR => 'hello',
                    Constants::TYPE_INSTANCE_DESCRIPTION_ATTR => 'world',
                ])
            ];
	}

    public function isCalled(string $manager): bool {
        return array_key_exists($manager, $this->isCalled) && boolval($this->isCalled[$manager]);
    }

    public function getModel1ItsCalledWith(string $manager): ?Model {
        return array_key_exists($manager, $this->lastModel1ItsCalledWith)
            ? $this->lastModel1ItsCalledWith[$manager] : null;
    }

    public function getModel2ItsCalledWith(string $manager): ?Model {
        return array_key_exists($manager, $this->lastModel2ItsCalledWith)
            ? $this->lastModel2ItsCalledWith[$manager] : null;
    }

    private function callRenderer(string $manager, Model $model1, Model $model2): void {
        $this->isCalled[$manager] = true;
        $this->lastModel1ItsCalledWith[$manager] = $model1;
        $this->lastModel2ItsCalledWith[$manager] = $model2;
    }
}
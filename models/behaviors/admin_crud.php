<?php
class AdminCrudBehavior extends ModelBehavior {
	public function adminAdd($Model, $data = null) {
		if (empty($data)) {
			return;
		}

		$Model->create();
		$result = $Model->save($data);
		if ($result !== false) {
			$Model->data = array_merge($data, $result);
			return true;
		} else {
			throw $this->__validateError($Model);
		}
	}

	private function __validateError($Model) {
		$humanize = Inflector::humanize(Inflector::underscore($Model->alias));
		$message = sprintf(__('Could not save the %s, please check your inputs.', true), $humanize);
		return new OutOfBoundsException($message);
	}

	public function adminEdit($Model, $id = null, $data = null) {
		$item = $this->__getItem($Model, $id);

		if (empty($data)) {
			return $item;
		}

		$Model->set($item);
		$Model->set($data);

		$result = $Model->save(null, true);
		if ($result) {
			$Model->data = $result;
			return true;
		} else {
			return $data;
		}
	}

	public function adminView($Model, $id = null) {
		return $this->__getItem($Model, $id);
	}

	public function adminValidateAndDelete($Model, $id = null, $data = array()) {
		$item = $this->__getItem($Model, $id);

		if (empty($data)) {
			return $item;
		}

		$Model->validate = array(
			'id' => array('rule' => 'notEmpty'),
			'confirm' => array('rule' => '[1]')
		);

		$Model->set($data);
		if ($Model->validates() && $Model->delete($id)) {
			return true;
		}
		throw $this->__confirmationError($Model);
	}

	private function __getItem($Model, $id) {
		$item = $Model->find('first', array(
			'conditions' => array(
				"{$Model->alias}.{$Model->primaryKey}" => $id,
			)
		));

		if (empty($item)) {
			throw $this->__invalidError($Model);
		}

		return $item;
	}

	private function __invalidError($Model) {
		$humanize = Inflector::humanize(Inflector::underscore($Model->alias));
		$message = sprintf(__('Invalid %s', true), $humanize);
		return new OutOfBoundsException($message);
	}

	private function __confirmationError($Model) {
		$humanize = Inflector::humanize(Inflector::underscore($Model->alias));
		$message = sprintf(__('You need to confirm to delete this %s', true), $humanize);
		throw new Exception($message);
	}
}
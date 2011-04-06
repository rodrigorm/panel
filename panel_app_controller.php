<?php
class PanelAppController extends AppController {
	public function admin_index() {
		$this->{$this->modelClass}->recursive = 0;
		$this->set($this->__pluralName($this->modelClass), $this->paginate()); 
	}

	public function admin_view($id = null) {
		try {
			$item = $this->{$this->modelClass}->adminView($id);
		} catch (OutOfBoundsException $e) {
			$this->Session->setFlash($e->getMessage());
			return $this->redirect(array('action' => 'index'));
		}
		$this->set($this->__singularName($this->modelClass), $item);
	}

	public function admin_edit($id = null) {
		try {
			$result = $this->{$this->modelClass}->adminEdit($id, $this->data);
			if ($result === true) {
				$this->Session->setFlash(sprintf(__('%s saved', true), $this->__singularHumanName($this->modelClass)));
				$alias = $this->{$this->modelClass}->alias;
				$this->redirect(array('action' => 'view', $this->{$this->modelClass}->data[$alias]['id']));
			} else {
				$this->data = $result;
			}
		} catch (OutOfBoundsException $e) {
			$this->Session->setFlash($e->getMessage());
			$this->redirect('/');
		}
	}

	public function admin_add() {
		try {
			$result = $this->{$this->modelClass}->adminAdd($this->data);
			if ($result === true) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), $this->__singularHumanName($this->modelClass)));
				$this->redirect(array('action' => 'index'));
			}
		} catch (OutOfBoundsException $e) {
			$this->Session->setFlash($e->getMessage());
		} catch (Exception $e) {
			$this->Session->setFlash($e->getMessage());
			$this->redirect(array('action' => 'index'));
		}
	}

	public function admin_delete($id = null) {
		try {
			$result = $this->{$this->modelClass}->adminValidateAndDelete($id, $this->data);
			if ($result === true) {
				$this->Session->setFlash(sprintf(__('%s deleted', true), $this->__singularHumanName($this->modelClass)));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->set($this->__singularName($this->modelClass), $result);
			}
		} catch (Exception $e) {
			$this->Session->setFlash($e->getMessage());
			$this->redirect(array('action' => 'index'));
		}
	}

	private function __pluralName($name) {
		return Inflector::variable(Inflector::pluralize($name));
	}

	private function __singularName($name) {
		return Inflector::variable(Inflector::singularize($name));
	}

	private function __singularHumanName($name) {
		return Inflector::humanize(Inflector::underscore(Inflector::singularize($name)));
	}
}
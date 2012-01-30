<?php
App::Uses('AdminCrudBehavior', 'Panel.Model/Behavior');
App::uses('Model', 'Model');

class User extends Model {
	public $name = 'User';

	public $validate = array(
		'name' => array(
			'rule' => 'notempty',
			'required' => true,
			'allowEmpty' => false
		)
	);
}

class AdminCrudBehaviorTest extends CakeTestCase {
	public $fixtures = array(
		'plugin.panel.user'
	);

	public function startTest($method) {
		parent::startTest($method);

		$this->AdminCrud = new AdminCrudBehavior();
		$this->User = ClassRegistry::init('User');

		$fixture = new UserFixture();
		$this->record = array(
			'User' => $fixture->records[0]
		);
	}

	public function endTest($method) {
		parent::endTest($method);

		unset($this->AdminCrud);
		unset($this->User);
		ClassRegistry::flush();
	}

	public function testAdminAdd() {
		$data = $this->record;
		unset($data['User']['id']);
		$result = $this->AdminCrud->adminAdd($this->User, $data);
		$this->assertTrue($result);
	}

/**
 * @expectedException OutOfBoundsException
 * @expectedExceptionMessage Could not save the User, please check your inputs.
 */
	public function testAdminAddInvalid() {
		$data = $this->record;
		unset($data['User']['id']);
		unset($data['User']['name']);
		$this->AdminCrud->adminAdd($this->User, $data);
	}

/**
 * @expectedException OutOfBoundsException
 * @expectedExceptionMessage Could not save the Operator, please check your inputs.
 */
	public function testAdminAddExceptionWithAnotherAlias() {
		$data = $this->record;
		unset($data['User']['id']);
		unset($data['User']['name']);
		$this->User->alias = 'Operator';
		$this->AdminCrud->adminAdd($this->User, $data);
	}

	public function testAdminEdit() {
		$data = $this->record;
		$data['User']['name'] = 'New Name';

		$result = $this->AdminCrud->adminEdit($this->User, 1, $data);
		$this->assertTrue($result === true);

		$result = $this->User->read(null, 1);

		$this->assertEqual($result['User']['name'], $data['User']['name']);
	}

	public function testAdminWithoutData() {
		$result = $this->AdminCrud->adminEdit($this->User, 1, null);

		$expected = $this->User->read(null, 1);
		$this->assertEqual($result['User'], $expected['User']);
	}

	public function testAdminEditWithInvalidData() {
		$data = $this->record;
		$data['User']['name'] = null;
		
		$result = $this->AdminCrud->adminEdit($this->User, 1, $data);
		$this->assertEqual($result, $data);
	}

/**
 * @expectedException OutOfBoundsException
 * @expectedExceptionMessage Invalid User
 */
	public function testAdminEditWithWrongId() {
		$this->AdminCrud->adminEdit($this->User, 'wrong_id', $this->record);
	}

/**
 * @expectedException OutOfBoundsException
 * @expectedExceptionMessage Invalid Operator
 */
	public function testAdminEditExceptionWithAnotherAlias() {
		$this->User->alias = 'Operator';
		$this->AdminCrud->adminEdit($this->User, 'wrong_id', $this->record);
	}

	public function testAdminView() {
		$result = $this->AdminCrud->adminView($this->User, 1);
		$this->assertTrue(isset($result['User']));
		$this->assertEqual($result['User']['id'], 1);
	}

/**
 * @expectedException OutOfBoundsException
 * @expectedExceptionMessage Invalid User
 */
	public function testAdminViewWithWrongId() {
		$this->AdminCrud->adminView($this->User, 'wrong_id');
	}

	public function testAdminValidateAndDelete() {
		$postData = array(
			'User' => array(
				'confirm' => 1
			)
		);
		$result = $this->AdminCrud->adminValidateAndDelete($this->User, 1, $postData);
		$this->assertTrue($result);
	}

/**
 * @expectedException OutOfBoundsException
 * @expectedExceptionMessage Invalid User
 */
	public function testAdminValidateAndDeleteWithWrongId() {
		$this->AdminCrud->adminValidateAndDelete($this->User, 'wrogn_id', array());
	}

/**
 * @expectedException UnexpectedValueException
 * @expectedExceptionMessage You need to confirm to delete this User
 */
	public function testAdminValidateAndDeleteWithoutConfirmation() {
		$postData = array(
			'User' => array(
				'confirm' => 0
			)
		);
		$this->AdminCrud->adminValidateAndDelete($this->User, 1, $postData);
	}

/**
 * @expectedException UnexpectedValueException
 * @expectedExceptionMessage You need to confirm to delete this Operator
 */
	public function testAdminValidateAndDeleteWithoutConfirmationAnotherAlias() {
		$postData = array(
			'Operator' => array(
				'confirm' => 0
			)
		);
		$this->User->alias = 'Operator';
		$this->AdminCrud->adminValidateAndDelete($this->User, 1, $postData);
	}
}
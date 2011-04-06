<?php
App::import('Behavior', 'Admin.AdminCrud');

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

class AdminCrudTestCase extends CakeTestCase {
	public $fixtures = array(
		'plugin.admin.user'
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

	public function testAdminAddInvalid() {
		$data = $this->record;
		unset($data['User']['id']);
		unset($data['User']['name']);

		$this->expectException(new OutOfBoundsException('Could not save the User, please check your inputs.'));
		$this->AdminCrud->adminAdd($this->User, $data);
	}

	public function testAdminAddExceptionWithAnotherAlias() {
		$data = $this->record;
		unset($data['User']['id']);
		unset($data['User']['name']);
		$this->User->alias = 'Operator';

		$this->expectException(new OutOfBoundsException('Could not save the Operator, please check your inputs.'));
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

	public function testAdminEditWithWrongId() {
		$this->expectException(new OutOfBoundsException('Invalid User'));
		$this->AdminCrud->adminEdit($this->User, 'wrong_id', $this->record);
	}

	public function testAdminEditExceptionWithAnotherAlias() {
		$this->expectException(new OutOfBoundsException('Invalid Operator'));
		$this->User->alias = 'Operator';
		$this->AdminCrud->adminEdit($this->User, 'wrong_id', $this->record);
	}

	public function testAdminView() {
		$result = $this->AdminCrud->adminView($this->User, 1);
		$this->assertTrue(isset($result['User']));
		$this->assertEqual($result['User']['id'], 1);
	}

	public function testAdminViewWithWrongId() {
		$this->expectException(new OutOfBoundsException('Invalid User'));
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

	public function testAdminValidateAndDeleteWithWrongId() {
		$this->expectException(new OutOfBoundsException('Invalid User'));
		$this->AdminCrud->adminValidateAndDelete($this->User, 'wrogn_id', array());
	}

	public function testAdminValidateAndDeleteWithoutConfirmation() {
		$this->expectException(new Exception('You need to confirm to delete this User'));
		$postData = array(
			'User' => array(
				'confirm' => 0
			)
		);
		$this->AdminCrud->adminValidateAndDelete($this->User, 1, $postData);
	}

	public function testAdminValidateAndDeleteWithoutConfirmationAnotherAlias() {
		$this->expectException(new Exception('You need to confirm to delete this Operator'));
		$postData = array(
			'Operator' => array(
				'confirm' => 0
			)
		);
		$this->User->alias = 'Operator';
		$this->AdminCrud->adminValidateAndDelete($this->User, 1, $postData);
	}
}
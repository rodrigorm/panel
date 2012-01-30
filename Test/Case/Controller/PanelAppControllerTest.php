<?php
App::import('Controller', 'Panel.App');

App::import('Model', 'App');
class User extends AppModel {
	public $name = 'User';

	public $actsAs = array(
		'Panel.AdminCrud'
	);
}

class UsersController extends PanelAppController {
	public $redirect = null;

	public function redirect($redirect) {
		$this->redirect = $redirect;
	}
}

class PanelAppControllerTest extends ControllerTestCase {
	public $fixtures = array(
		'plugin.panel.user'
	);

	public function startTest($method) {
		parent::startTest($method);
		$this->Users = $this->generate('Users');
		$this->Users->constructClasses();
		$this->Users->params = array(
			'named' => array(),
			'pass' => array(),
			'url' => array()
		);
		$fixture = new UserFixture();
		$this->record = array('User' => $fixture->records[0]);
	}

	public function endTest($method) {
		parent::endTest($method);
		unset($this->Users);
		ClassRegistry::flush();
	}

	public function assertFlash($message) {
		$flash = $this->Users->Session->read('Message.flash');
		$this->assertEqual($flash['message'], $message);
		$this->Users->Session->delete('Message.flash');
	}

	public function testInstance() {
		$this->assertIsA($this->Users, 'UsersController');
		$this->assertIsA($this->Users->User, 'User');
	}

	public function testAdminIndex() {
		$this->Users->admin_index();
		$this->assertTrue(!empty($this->Users->viewVars['users']));
	}

	public function testAdminView() {
		$this->Users->admin_view(1);
		$this->assertTrue(!empty($this->Users->viewVars['user']));
	}

	public function testAdminViewWrongId() {
		$this->Users->admin_view('WRONG-ID');
		$this->assertEqual($this->Users->redirect, array('action' => 'index'));
		$this->assertFlash('Invalid User');
	}

	public function testAdminEdit() {
		$this->Users->data = $this->record;
		$this->Users->admin_edit(1);
		$this->assertEqual($this->Users->redirect, array('action' => 'view', 1));
		$this->assertFlash('User saved');
	}

	public function testAdminEditWithoutData() {
		$this->Users->admin_edit(1);
		$this->assertEqual($this->Users->data['User'], $this->record['User']);
	}

	public function testAdminAdd() {
		$this->Users->data = $this->record;
		unset($this->Users->request->data['User']['id']);
		$this->Users->admin_add();
		$this->assertEqual($this->Users->redirect, array('action' => 'index'));
		$this->assertFlash('The User has been saved');
	}

	public function testAdminDelete() {
		$this->Users->data = array('User' => array('confirmed' => 1));
		$this->Users->admin_delete(1);
		$this->assertEqual($this->Users->redirect, array('action' => 'index'));
		$this->assertFlash('User deleted');
	}

	public function testAdminDeleteWrongId() {
		$this->Users->admin_delete('WRONG-ID');
		$this->assertEqual($this->Users->redirect, array('action' => 'index'));
		$this->assertFlash('Invalid User');
	}

	public function testAdminDeleteWithoutConfirmation() {
		$this->Users->admin_delete(1);
		$this->assertTrue(!empty($this->Users->viewVars['user']));
	}
}
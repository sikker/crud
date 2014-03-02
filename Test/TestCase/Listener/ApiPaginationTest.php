<?php
namespace Crud\Test\TestCase\Listener;

use Crud\TestSuite\TestCase;

/**
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class ApiPaginationTest extends TestCase {

/**
 * Test implemented events
 *
 * @covers \Crud\Listener\ApiPagination::implementedEvents
 * @return void
 */
	public function testImplementedEvents() {
		$Instance = $this
			->getMockBuilder('\Crud\Listener\ApiPagination')
			->disableOriginalConstructor()
			->setMethods(null)
			->getMock();

		$result = $Instance->implementedEvents();
		$expected = [
			'Crud.beforeRender' => ['callable' => 'beforeRender', 'priority' => 75]
		];
		$this->assertEquals($expected, $result);
	}

/**
 * Test that non-API requests don't get processed
 *
 * @covers \Crud\Listener\ApiPagination::beforeRender
 * @return void
 */
	public function testBeforeRenderNotApi() {
		$Request = $this
			->getMockBuilder('\Cake\Network\Request')
			->setMethods(['is'])
			->getMock();
		$Request
			->expects($this->once())
			->method('is')
			->with('api')
			->will($this->returnValue(false));

		$Instance = $this
			->getMockBuilder('\Crud\Listener\ApiPagination')
			->disableOriginalConstructor()
			->setMethods(['_request'])
			->getMock();
		$Instance
			->expects($this->once())
			->method('_request')
			->will($this->returnValue($Request));

		$Instance->beforeRender(new \Cake\Event\Event('something'));
	}

/**
 * Test that API requests do not get processed
 * if there is no pagination data
 *
 * @covers \Crud\Listener\Api::beforeRender
 * @return void
 */
	public function testBeforeRenderNoPaginationData() {
		$Request = $this
			->getMockBuilder('\Cake\Network\Request')
			->setMethods(['is'])
			->getMock();
		$Request
			->expects($this->once())
			->method('is')
			->with('api')
			->will($this->returnValue(true));

		$Controller = $this
			->getMockBuilder('\Cake\Controller\Controller')
			->disableOriginalConstructor()
			->setMethods(null)
			->getMock();

		$Instance = $this
			->getMockBuilder('\Crud\Listener\ApiPagination')
			->disableOriginalConstructor()
			->setMethods(['_request', '_controller'])
			->getMock();
		$Instance
			->expects($this->once())
			->method('_request')
			->will($this->returnValue($Request));
		$Instance
			->expects($this->once())
			->method('_controller')
			->will($this->returnValue($Controller));

		$Request->paging = ['MyModel' => []];
		$Controller->modelClass = 'MyModel';

		$Instance->beforeRender(new \Cake\Event\Event('something'));
	}

/**
 * Test that API requests do not get processed
 * if there if pagination data is NULL
 *
 * @covers \Crud\Listener\Api::beforeRender
 * @return void
 */
	public function testBeforeRenderPaginationDataIsNull() {
		$Request = $this
			->getMockBuilder('\Cake\Network\Request')
			->setMethods(['is'])
			->getMock();
		$Request
			->expects($this->once())
			->method('is')
			->with('api')
			->will($this->returnValue(true));

		$Controller = $this
			->getMockBuilder('\Cake\Controller\Controller')
			->disableOriginalConstructor()
			->setMethods(null)
			->getMock();

		$Instance = $this
			->getMockBuilder('\Crud\Listener\ApiPagination')
			->disableOriginalConstructor()
			->setMethods(['_request', '_controller'])
			->getMock();
		$Instance
			->expects($this->once())
			->method('_request')
			->will($this->returnValue($Request));
		$Instance
			->expects($this->once())
			->method('_controller')
			->will($this->returnValue($Controller));

		$Request->paging = null;
		$Controller->modelClass = 'MyModel';

		$Instance->beforeRender(new \Cake\Event\Event('something'));
	}

/**
 * Test that API requests do get processed
 * if there is pagination data
 *
 * @covers \Crud\Listener\Api::beforeRender
 * @return void
 */
	public function testBeforeRenderWithPaginationData() {
		$Request = $this
			->getMockBuilder('\Cake\Network\Request')
			->setMethods(['is'])
			->getMock();
		$Request
			->expects($this->once())
			->method('is')
			->with('api')
			->will($this->returnValue(true));
		$Request->paging = [
			'MyModel' => [
				'pageCount' => 10,
				'page' => 2,
				'nextPage' => true,
				'prevPage' => true,
				'count' => 100,
				'limit' => 10
			]
		];

		$expected = [
			'page_count' => 10,
			'current_page' => 2,
			'has_next_page' => true,
			'has_prev_page' => true,
			'count' => 100,
			'limit' => 10
		];

		$Controller = $this
			->getMockBuilder('\Cake\Controller\Controller')
			->disableOriginalConstructor()
			->setMethods(['set'])
			->getMock();
		$Controller
			->expects($this->once())
			->method('set')
			->with('pagination', $expected);

		$Action = $this
			->getMockBuilder('\Crud\Action\Base')
			->disableOriginalConstructor()
			->setMethods(['config'])
			->getMock();
		$Action
			->expects($this->once())
			->method('config')
			->with('serialize.pagination', 'pagination');

		$Instance = $this
			->getMockBuilder('\Crud\Listener\ApiPagination')
			->disableOriginalConstructor()
			->setMethods(['_request', '_controller', '_action'])
			->getMock();
		$Instance
			->expects($this->once())
			->method('_request')
			->will($this->returnValue($Request));
		$Instance
			->expects($this->once())
			->method('_controller')
			->will($this->returnValue($Controller));
		$Instance
			->expects($this->once())
			->method('_action')
			->will($this->returnValue($Action));

		$Controller->modelClass = 'MyModel';

		$Instance->beforeRender(new \Cake\Event\Event('something'));
	}
}

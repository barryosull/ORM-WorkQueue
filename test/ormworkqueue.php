<?
/**
 * @group App
 * @group ORMWorkQueue
 */
class Test_ORMWorkQueue extends \Fuel\Core\TestCase
{
	protected $date;
	protected $obj_with_working_func;
	protected $obj_with_failing_func;

	public function setUp()
	{
		Model_ORMWorkQueue::find()->delete();

		$this->date = new DateTime();
		$this->obj_with_working_func = new WorkingClass();
		$this->obj_with_failing_func = new FailingClass();

		Model_ORMWorkQueue::append_task(
			$this->obj_with_working_func, 
			'run'
		);
	}

	public function test_pop_off_task()
	{
		$task = Model_ORMWorkQueue::pop_off_task();

		$this->assertEquals('Model_ORMWorkQueue', get_class($task));
	}

	public function test_has_pending_tasks()
	{
		$this->assertTrue(Model_ORMWorkQueue::has_pending_tasks());
	}

	public function test_pop_off_temporarily_removes_task()
	{
		$this->assertTrue(Model_ORMWorkQueue::has_pending_tasks());
		$task = Model_ORMWorkQueue::pop_off_task();

		$this->assertFalse(Model_ORMWorkQueue::has_pending_tasks());
	}

	public function test_popped_off_tasks_reappear_after_1_minute()
	{
		$this->assertTrue(Model_ORMWorkQueue::has_pending_tasks());
		$task = Model_ORMWorkQueue::pop_off_task();

		$this->assertFalse(Model_ORMWorkQueue::has_pending_tasks());

		Model_ORMWorkQueue::set_current_date(new Datetime('+1 minute'));

		$this->assertTrue(Model_ORMWorkQueue::has_pending_tasks());
		$same_task = Model_ORMWorkQueue::pop_off_task();

		$this->assertEquals($task->get_id(), $same_task->get_id());
	}

	/**
	 * @expectedException Model_ORMWorkQueueException
	 */
	public function test_pop_off_with_no_task_causes_exception()
	{
		Model_ORMWorkQueue::find()->delete();
		$task = Model_ORMWorkQueue::pop_off_task();
	}

	public function test_has_pending_task_for_model()
	{
		$this->assertTrue(Model_ORMWorkQueue::has_pending_task_for_model($this->obj_with_working_func));

		$this->assertFalse(Model_ORMWorkQueue::has_pending_task_for_model($this->obj_with_failing_func));
	}

	public function test_is_pending()
	{
		$task = Model_ORMWorkQueue::pop_off_task();
		$this->assertTrue($task->is_pending());
	}

	public function test_that_run_calls_model_method()
	{
		$task = Model_ORMWorkQueue::pop_off_task();
		
		$this->assertFalse(WorkingClass::$has_run);

		$task->run();

		$this->assertTrue(WorkingClass::$has_run);
	}

	public function test_has_finished()
	{
		$task = Model_ORMWorkQueue::pop_off_task();
		$task->run();
		$this->assertTrue($task->has_finished());
	}

	public function test_fails_after_5_tries()
	{

		Model_ORMWorkQueue::find()->delete();;
		$this->pop_on_failing_class();

		$task = Model_ORMWorkQueue::pop_off_task();
		$task->run();
		$task->run();
		$task->run();
		$task->run();
		$this->assertTrue($task->is_pending());
		$task->run();

		$this->assertTrue($task->has_failed());
	}

	private function pop_on_failing_class()
	{
		$class = new FailingClass();
		Model_ORMWorkQueue::append_task($class, 'fail');
	}

	public function test_has_successfull_task_returns_false()
	{
		$this->assertFalse(Model_ORMWorkQueue::has_successfull_task());
	}

	public function test_has_successfull_task_returns_true()
	{
		$task = Model_ORMWorkQueue::pop_off_task();
		$task->run();
		$this->assertTrue(Model_ORMWorkQueue::has_successfull_task());
	}

	/**
	 * @expectedException Model_ORMWorkQueueException
	 */
	public function test_get_last_successfull_task_throws_exception_when_no_tasks()
	{
		$successfull_task = Model_ORMWorkQueue::get_last_successfull_task();
	}

	public function test_get_last_successfull_task()
	{
		$task = Model_ORMWorkQueue::pop_off_task();
		$task->run();

		$successfull_task = Model_ORMWorkQueue::get_last_successfull_task();
		$this->assertEquals($task->get_id(), $successfull_task->get_id());
	}
}

class WorkingClass implements FuelORM
{
	public static $has_run = false;
	
	public static function find($id)
	{
		return new self();
	}

	public function get_id()
	{
		return 1;
	}

	public function run()
	{
		self::$has_run = true;
	}
}

class FailingClass implements FuelORM
{
	public static function find($id)
	{
		return new self();
	}

	public function get_id()
	{
		return 1;
	}

	public function fail()
	{
		throw new Exception("I fail like a pro");	
	}
}
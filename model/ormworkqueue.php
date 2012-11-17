<?php

class Model_ORMWorkQueueException extends Exception {}

class Model_ORMWorkQueue extends \Orm\Model
{
	protected static $_properties = array(
		'id',
		'retry_count',
		'status',
		'model_name',
		'model_id',
		'function_to_run',
		'next_visible_at',
		'created_at',
		'updated_at'
	);

	protected static $_table_name = 'workqueues';

	protected static $_observers = array(
		'Orm\Observer_CreatedAt' => array(
			'events' => array('before_insert'),
			'mysql_timestamp' => false,
		),
		'Orm\Observer_UpdatedAt' => array(
			'events' => array('before_save'),
			'mysql_timestamp' => false,
		),
	);

	public static function append_task($model, $function_to_run)
	{
		$task = new self();
		$task->retry_count = 0;
		$task->status = 'pending';
		$task->model_name = get_class($model);
		$task->model_id = $model->get_id();
		$task->function_to_run = $function_to_run;
		$task->next_visible_at = time();
		$task->save();
	}

	public static function pop_off_task()
	{
		$task =	self::find()
				->where('status', 'pending')
				->where('next_visible_at', '<=', self::get_time() )
				->order_by('next_visible_at', 'asc')
				->get_one();
		
		if(!$task)
		{
			throw new Model_ORMWorkQueueException("There are no more pending tasks");
		}

		$task->increment_next_visible();

		return $task;
	}

	private function increment_next_visible()
	{
		$this->next_visible_at = time()+60;
		$this->save();
	}

	public static function has_pending_tasks()
	{
		$count =	self::get_next_pending_query()
					->count();

		return (bool)$count;
	}

	private static function get_next_pending_query()
	{
		return 	self::find()
				->where('status', 'pending')
				->where('next_visible_at', '<=', self::get_time() );
	}

	public static function has_pending_task_for_model($model)
	{
		$count =	self::get_next_pending_query()
					->where('model_name', get_class($model))
					->where('model_id', $model->get_id())
					->count();

		return (bool)$count;
	}

	public static function has_successfull_task()
	{
		return (bool)self::get_successfull_task_query()
					->count();
	}

	private static function get_successfull_task_query()
	{
		return self::find()
				->where('status', 'finished');
	}

	public static function get_last_successfull_task()
	{
		$task = self::get_successfull_task_query()
				->order_by('updated_at', 'DESC')
				->get_one();

		if(!$task)
		{
			throw new Model_ORMWorkQueueException("There are no successfull tasks");
		}
		return $task;
	}

	
	protected static $time;

	public static function set_current_date($date)
	{
		self::$time = $date->getTimestamp();
	}

	private static function get_time()
	{
		if(self::$time)
		{
			return self::$time;
		}
		return time();
	}

	public function is_pending()
	{
		return ($this->status == 'pending');
	}

	public function has_finished()
	{
		return ($this->status == 'finished');
	}

	public function has_failed()
	{
		return ($this->status == 'failed');
	}

	public function run()
	{
		$model_name = $this->model_name;
		$model_id = $this->model_id;
		$model = $model_name::find($model_id);
		$function_to_run = $this->function_to_run;
		
		$this->retry_count++;
		
		$this->run_model_function($model, $function_to_run);
	}

	private function run_model_function($model, $function_to_run)
	{
		try
		{
			$model->$function_to_run();
			$this->status = 'finished';
		}
		catch(Exception $e)
		{
			if($this->is_over_retry_count())
			{
				$this->status = 'failed';
			}
		}
		$this->save();
	}

	private function is_over_retry_count()
	{
		return (bool)($this->retry_count >= 5); 
	} 
}

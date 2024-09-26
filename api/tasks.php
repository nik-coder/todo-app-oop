<?php

class Task {
  public $id;
  public $task;

  public function __construct($id, $task) {
    $this->id = $id;
    $this->task = $task;
  }
}

class TaskStorage {
  private $path;

  public function __construct($path) {
    $this->path = $path;
  }

  public function getPath() {
    return $this->path;
  }
}

class TaskReader {
  private $filePath;

  public function __construct($filePath) {
    $this->filePath = $filePath;
  }

  public function readTasks() {
    if (file_exists($this->filePath->getPath())) {
      $data = file_get_contents($this->filePath->getPath());
      return json_decode($data, true);
    }
    return [];
  }
}

class TaskWriter {
  private $filePath;

  public function __construct($filePath) {
    $this->filePath = $filePath;
  }

  public function writeTasks($tasks) {
    file_put_contents($this->filePath->getPath(), json_encode($tasks, JSON_PRETTY_PRINT));
  }
}

class TaskController {
  private $filePath;
  private $taskReader;
  private $taskWriter;

  public function __construct($filePath) {
    $this->filePath = $filePath;
    $this->taskReader = new TaskReader($filePath);
    $this->taskWriter = new TaskWriter($filePath);
  }

  public function handleRequest() {
    $method = $_SERVER['REQUEST_METHOD'];
    switch ($method) {
      case 'GET':
        echo json_encode($this->taskReader->readTasks());
        break;
      case 'POST':
        $tasks = $this->taskReader->readTasks();
        $newTask = json_decode(file_get_contents('php://input'), true);
        $newTask['id'] = uniqid();
        $tasks[] = $newTask;
        $this->taskWriter->writeTasks($tasks);
        echo json_encode($newTask);
        break;
      case 'PUT':
        $tasks = $this->taskReader->readTasks();
        $updatedTask = json_decode(file_get_contents('php://input'), true);
        foreach ($tasks as &$task) {
          if ($task['id'] === $updatedTask['id']) {
            $task['task'] = $updatedTask['task'];
            break;
          }
        }
        $this->taskWriter->writeTasks($tasks);
        echo json_encode($updatedTask);
        break;
      case 'DELETE':
        $tasks = $this->taskReader->readTasks();
        $deleteId = json_decode(file_get_contents('php://input'), true)['id'];
        $tasks = array_filter($tasks, fn($task) => $task['id'] !== $deleteId);
        $this->taskWriter->writeTasks(array_values($tasks));
        echo json_encode(['status' => 'success']);
        break;
      default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
        break;
    }
  }
}

$filePath = new TaskStorage('../data/tasks.json');
$taskManager = new TaskController($filePath);
$taskManager->handleRequest();

header('Content-Type: application/json');
?>
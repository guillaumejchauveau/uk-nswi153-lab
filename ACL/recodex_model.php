<?php


/**
 * Trivial implementation of user entity.
 */
class User implements IUser
{
	private $name;
	private $surname;
	private $role;

	public function __construct(string $name, string $surname, string $role)
	{
		$this->name = $name;
		$this->surname = $surname;
		$this->role = $role;
	}

	public function getFullName(): string
	{
		return "$this->name $this->surname";
	}

	public function getRole(): string
	{
		return $this->role;
	}

	public function setRole(string $role)
	{
		$this->role = $role;
	}
}



/**
 * Trivial implementation of the project entity.
 */
class Project implements IProject
{
	private $name;
	private $description = '';
	private $tasks = [];
	
	private $parent;
	private $children = [];

	private $managers = [];
	private $teamMembers = [];
	

	public function __construct(string $name, IProject $parent = null)
	{
		$this->name = $name;
		$this->parent = $parent;
	}


	public function getName(): string
	{
		return $this->name;
	}

	public function setName(string $name)
	{
		$this->name = $name;
	}

	public function getDescription(): string
	{
		return $this->description;
	}

	public function setDescription(string $description)
	{
		$this->description = $description;
	}


	public function getTaskStatus(): array
	{
		return $this->tasks;
	}

	public function setTaskComplete(string $taskId)
	{
		if (!array_key_exists($taskId, $this->tasks)) {
			throw new Exception("Task '$taskId' does not exist.");
		}
		$this->tasks[$taskId] = true;
	}

	public function addTask(string $taskId)
	{
		if (array_key_exists($taskId, $this->tasks)) {
			throw new Exception("Task '$taskId' alerady exists.");
		}
		$this->tasks[$taskId] = false;
	}

	public function deleteTask(string $taskId)
	{
		if (!array_key_exists($taskId, $this->tasks)) {
			throw new Exception("Task '$taskId' does not exist.");
		}
		unset($this->tasks[$taskId]);
	}


	public function getParentProject(): ?IProject
	{
		return $this->parent;
	}

	public function getSubProjects(): array
	{
		return $this->children;
	}

	public function createSubProject(string $name): IProject
	{
		$project = new Project($name, $this);
		$this->children[] = $project;
		return $project;
	}

	public function deleteSubProject(IProject $project)
	{
		if ($project->getParentProject() !== $this) {
			throw new Exception("Given project is not a sub-project.");
		}

		if ($project instanceof Project) {
			$project->parent = null;
		}
		$this->children = array_filter($this->children, function($child) use ($project) { return $child !== $project; });
	}


	public function getManagers(): array
	{
		return $this->managers;
	}

	public function addManager(IUser $user)
	{
		if (array_search($user, $this->managers, true) !== false) {
			throw new Exception("Given user is already a manager of this project.");
		}

		// Remove the user from members, since this is a promotion...
		if (array_search($user, $this->teamMembers, true) !== false) {
			$this->teamMembers = array_filter($this->teamMembers, function ($member) use ($user) { return $member !== $user; });
		}

		$this->managers[] = $user;
	}

	public function removeManager(IUser $user)
	{
		$this->managers = array_filter($this->managers, function ($manager) use ($user) { return $manager !== $user; });
	}


	public function getTeamMembers(): array
	{
		return $this->teamMembers;
	}

	public function addTeamMember(IUser $user)
	{
		if (array_search($user, $this->teamMembers, true) !== false) {
			throw new Exception("Given user is already a team member of this project.");
		}

		if (array_search($user, $this->managers, true) !== false) {
			throw new Exception("Given user is already a manager of this project.");
		}

		$this->teamMembers[] = $user;
	}

	public function removeTeamMember(IUser $user)
	{
		$this->teamMembers = array_filter($this->teamMembers, function ($member) use ($user) { return $member !== $user; });
	}
}


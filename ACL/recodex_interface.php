<?php


/**
 * This security model is perhaps a little too fine grained as it
 * controls access to every method of every object in the data model.
 * However, bare in mind that it is for educational purposes only and
 * we need to bloat the number of resources x actions cartesian product
 * so that some optimizations become necessary in the ACL implementation.
 */
interface ISecurityModel
{
	/**
	 * The main security model method. It verifies whether given authority is allowed
	 * to execute given method on given model object.
	 * @param IUser $user The authority that wishes to perform the action.
	 * @param object $resource One of the objects from the model.
	 * @param string $action Name of the method to be called.
	 * @return bool True if the user is allowed to perform the action, false otherwise.
	 */
	public function hasPermissions(IUser $user, object $resource, string $action): bool;
}



/*
 * Data Model Interfaces
 */

/**
 * Interface for user objects.
 */
interface IUser
{
	const ROLE_EMPLOYEE = 'employee';
	const ROLE_CEO = 'ceo';

	const ROLE_AUDITOR = 'auditor';
	const ROLE_ADMIN = 'admin';

	/**
	 * Return the full user string.
	 * @return string
	 */
	public function getFullName(): string;

	/**
	 * Return the role of the user as a string.
	 * @return string
	 */
	public function getRole(): string;

	/**
	 * Set the new role of the user.
	 * @param string $role
	 */
	public function setRole(string $role);
}



/**
 * Interface for projects. Each project is basically a node in a tree.
 * Furthermore, projects have associated users as managers or team members
 * and project hold name, description, and a list of tasks with completion indicators.
 */
interface IProject
{
	/**
	 * Return name of the project.
	 * @return string
	 */
	public function getName(): string;

	/**
	 * Updates the project name.
	 * @param string $name Unique name of the project.
	 */
	public function setName(string $name);

	/**
	 * Retuurn detailed description of the project.
	 * @return string
	 */
	public function getDescription(): string;

	/**
	 * Updates the description of the project.
	 * @param string $description
	 */
	public function setDescription(string $description);


	/**
	 * Return task IDs and their completion statuses.
	 * The completion status is a bool value (true means task is completed).
	 * @return array [ taskId => completion status ]
	 */
	public function getTaskStatus(): array;

	/**
	 * Mark given task as complete (set its flag to true).
	 * @param string $taskId
	 */
	public function setTaskComplete(string $taskId);

    /**
     * Add a new task to the project.
     * @param string $taskId ID of the new task, which must be unique within a project.
     */
	public function addTask(string $taskId);

	/**
	 * Remove task of given ID.
	 * @param $taskId
	 */
	public function deleteTask(string $taskId);


	/**
	 * Return parent project. If this is a root project, null is returned.
	 * @return IProject|null
	 */
	public function getParentProject(): ?IProject;

	/**
	 * Return all nested projects.
	 * @return IProject[]
	 */
	public function getSubProjects(): array;

	/**
	 * Create new project and attach it as a sub-project.
	 * @param string $name Unique name of the project.
	 */
	public function createSubProject(string $name): IProject;

	/**
	 * Delete a sub-project.
	 * @param IProject $project
	 */
	public function deleteSubProject(IProject $project);


	/**
	 * Return all project managers.
	 * @return IUser[]
	 */
	public function getManagers(): array;

	/**
	 * Make given user one of the managers of the project.
	 * If the user is already a team member, he/she is promoted (i.e., removed from the team).
	 * @param IUser $user
	 */
	public function addManager(IUser $user);

	/**
	 * Remove given user from the project manager position.
	 * @param IUser $user
	 */
	public function removeManager(IUser $user);


	/**
	 * Return all regular team members.
	 * @return IUser[]
	 */
	public function getTeamMembers(): array;

	/**
	 * Add given user as a team member of the project.
	 * @param IUser $user
	 */
	public function addTeamMember(IUser $user);

	/**
	 * Remove user from the project team.
	 * @param IUser $user
	 */
	public function removeTeamMember(IUser $user);
}

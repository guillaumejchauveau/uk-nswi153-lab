<?php

/**
 * Special container that works as associative array but it may
 * register regular expressions as keys as well.
 */
class IndexedContainer
{
    private $items = [];        // regular items
    private $regexItems = [];    // items matched by regex keys


    /**
     * Simple key is a key which is not valid regular expression.
     * @param string $key
     * @return bool
     */
    private function isSimpleKey(string $key)
    {
        return @preg_match($key, null) === false;    // key is not a valid regular expression
    }

    /**
     * Constructor which also fills the index using associative array.
     * @param array $data Initial data to be added.
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            $this->add($key, $value);
        }
    }

    /**
     * Add new record identified by either simple key or regular expression.
     * Empty key is considered to be a default (will match any key in find operation).
     * @param string $keyOrRegex
     * @param $value
     */
    public function add(string $keyOrRegex, $value)
    {
        if ($this->isSimpleKey($keyOrRegex)) {
            $this->items[$keyOrRegex] = $value;
        } else {
            $this->regexItems[$keyOrRegex] = $value;
        }
    }

    /**
     * Find all values that could be associated with given key.
     * @param string $key
     * @return array
     */
    public function find(string $key)
    {
        $res = [];

        // Find the key as simple key (exact match).
        if (array_key_exists($key, $this->items)) {
            $res[] = $this->items[$key];
        }

        // Find first matching regex.
        foreach ($this->regexItems as $regex => $value) {
            if (preg_match($regex, $key)) {
                $res[] = $value;
            }
        }

        // Return default if exists.
        if (array_key_exists('', $this->items)) {
            $res[] = $this->items[''];
        }

        return $res;
    }

    /**
     * Find exact match of a key or regex value.
     * @param string $keyOrRegex
     * @return mixed|null
     */
    public function findExactMatch(string $keyOrRegex)
    {
        // Find the key as simple key (exact match).
        if (array_key_exists($keyOrRegex, $this->items)) {
            return $this->items[$keyOrRegex];
        }

        // Find the key as simple key (exact match).
        if (array_key_exists($keyOrRegex, $this->regexItems)) {
            return $this->regexItems[$keyOrRegex];
        }

        return null;
    }
}


/**
 * Helper class that aggregates policy functions (checks) of Project entity.
 * These methods must have exactly two args (user, project) and they can be
 * referred by name from the security model itself.
 */
class SecurityModelProjectACLPolicies
{
    /**
     * Verify whether the user is a manager of this project or inherits manager privileges from any of the ancestors.
     * @param IUser $user
     * @param IProject $project
     * @return bool
     */
    public function isManager(IUser $user, IProject $project): bool
    {
        return array_search($user, $project->getManagers(), true) !== false
            || ($project->getParentProject() && $this->isManager($user, $project->getParentProject()));
    }

    public function isMember(IUser $user, IProject $project): bool
    {
        return array_search($user, $project->getTeamMembers(), true) !== false;
    }

    public function isParentMember(IUser $user, IProject $project): bool
    {
        return array_search($user, $project->getTeamMembers(), true) !== false
            || ($project->getParentProject() && $this->isParentMember($user, $project->getParentProject()));
    }

    public function isChildMemberOrManager(IUser $user, IProject $project): bool
    {
        if (array_search($user, $project->getTeamMembers(), true) !== false) {
            return true;
        }
        if (array_search($user, $project->getManagers(), true) !== false) {
            return true;
        }
        foreach ($project->getSubProjects() as $childProject) {
            if ($this->isChildMemberOrManager($user, $childProject)) {
                return true;
            }
        }
        return false;
    }

}


/**
 * Implementation of the security model using ACL rules.
 */
class SecurityModel implements ISecurityModel
{
    private $aclPolicies = [];
    private $aclRules;


    /**
     * The constructor should fill/load the rules for the model.
     */
    public function __construct()
    {
        // Dependency injection should have been used here, but let's not complicate things right now...
        $this->aclPolicies['project'] = new SecurityModelProjectACLPolicies();

        $employee = '(employee|ceo)';
        $basicInfo = 'get(Name|Description)';
        $this->aclRules = new IndexedContainer([
            "/^$employee:user:getFullName$/" => [],
            "/^$employee:project:get./" => ['isParentMember'],
            "/^$employee:project:$basicInfo$/" => ['isChildMemberOrManager'],
            "/^$employee:project:setTaskComplete$/" => ['isMember'],
            "/^$employee:project:(?!(addManager|removeManager)).+$/" => ['isManager'],
            "ceo:user:getRole" => [],
            "/^ceo:project:(?!(setTaskComplete|addTask|deleteTask)).+$/" => [],
            "/^admin:/" => [],
            "/^auditor:.+:get./" => []
        ]);
    }


    public function hasPermissions(IUser $user, object $resource, string $action): bool
    {
        if ($resource instanceof IProject) {
            $resourceType = 'project';
        } elseif ($resource instanceof IUser) {
            $resourceType = 'user';
        } else {
            return false;
        }
        foreach ($this->aclRules->find($user->getRole() . ":$resourceType:$action") as $policies) {
            $authorized = true;
            foreach ($policies as $policy) {
                if (!$this->aclPolicies[$resourceType]->$policy($user, $resource)) {
                    $authorized = false;
                    break;
                }
            }
            if ($authorized) {
                return true;
            }
        }
        return false;
    }
}

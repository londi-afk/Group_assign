<?php

namespace Kanboard\Plugin\Group_assign\Filter;

use Kanboard\Core\Filter\FilterInterface;
use Kanboard\Filter\BaseDateFilter;
use Kanboard\Model\TaskModel;
use Kanboard\Model\UserModel;

/**
 * Filter tasks by assignee
 *
 * @package filter
 * @author  Frederic Guillot
 */
class TaskAssigneeFilter extends BaseFilter implements FilterInterface
{
    /**
     * Current user id
     *
     * @access private
     * @var int
     */
    private $currentUserId = 0;

    /**
     * Set current user id
     *
     * @access public
     * @param  integer $userId
     * @return TaskAssigneeFilter
     */
    public function setCurrentUserId($userId)
    {
        $this->currentUserId = $userId;
        return $this;
    }

    /**
     * Get search attribute
     *
     * @access public
     * @return string[]
     */
    public function getAttributes()
    {
        return array('assignee');
    }

    /**
     * Apply filter
     *
     * @access public
     * @return string
     */
    public function apply()
    {
        if (is_int($this->value) || ctype_digit($this->value)) {
            $this->query->eq(TaskModel::TABLE.'.owner_id', $this->value);
            $this->query->addCondition(TaskModel::TABLE.".owner_gp IN (SELECT group_id FROM ".GroupMemberModel::TABLE." WHERE ".GroupMemberModel::TABLE.".user_id='$user_id')");
        } else {
            switch ($this->value) {
                case 'me':
                    $this->query->eq(TaskModel::TABLE.'.owner_id', $this->currentUserId);
                    $this->query->addCondition(TaskModel::TABLE.".owner_gp IN (SELECT group_id FROM ".GroupMemberModel::TABLE." WHERE ".GroupMemberModel::TABLE.".user_id='$user_id')");
                    break;
                case 'nobody':
                    $this->query->eq(TaskModel::TABLE.'.owner_id', 0);
                    break;
                default:
                    $this->query->beginOr();
                    $this->query->ilike(UserModel::TABLE.'.username', '%'.$this->value.'%');
                    $this->query->ilike(UserModel::TABLE.'.name', '%'.$this->value.'%');
                    $this->query->closeOr();
            }
        }
    }
}

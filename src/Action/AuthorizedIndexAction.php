<?php
declare(strict_types=1);

namespace App\Action;

use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Cake\Routing\Router;
use Crud\Action\IndexAction;

/**
 * Crud index action with table authorization and scoped queries.
 */
class AuthorizedIndexAction extends IndexAction
{
    use AuthorizedCrudActionTrait;

    /**
     * @return \Cake\Http\Response|null
     */
    protected function _handle(): ?Response
    {
        $this->authorizeTable('index');

        [$finder, $options] = $this->_extractFinder();
        $query = $this->_model()->find($finder, ...$options);
        $query = $this->applyAuthorizationScope($query, 'index');
        $subject = $this->_subject(['success' => true, 'query' => $query]);

        $this->_trigger('beforePaginate', $subject);
        try {
            $items = $this->_controller()->paginate($subject->query);
        } catch (NotFoundException $e) {
            /** @var \Cake\Core\Exception\CakeException $previous */
            $previous = $e->getPrevious();
            $pagingParams = $previous->getAttributes()['pagingParams'];

            $url = Router::reverseToArray($this->_request());
            $url['?']['page'] = $pagingParams['pageCount'];

            return $this->_controller()->redirect($url);
        }

        $subject->set(['entities' => $items]);

        $this->_trigger('afterPaginate', $subject);
        $this->_trigger('beforeRender', $subject);

        return null;
    }
}

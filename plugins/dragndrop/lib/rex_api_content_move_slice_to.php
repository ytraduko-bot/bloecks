<?php
/**
 * rex_api_content class which moves a slice AFTER a given element
 */
class rex_api_content_move_slice_to extends rex_api_content_move_slice
{
    public function execute()
    {
        if(!rex::getUser()->hasPerm(bloecks_dragndrop_backend::getPermName()))
        {
            throw new rex_api_exception(rex_i18n::msg('no_rights_to_this_function'));
        }

        $article_id = rex_request('article_id', 'int');
        $clang_id = rex_request('clang', 'int');
        $slice_id = rex_request('slice_id', 'int');
        $direction = rex_request('direction', 'string');
        $insertafter = rex_request('insertafter', 'int', null);
        $insertafter_prio = null;

        if($insertafter !== null && ($direction == 'moveup' || $direction == 'movedown'))
        {
            $slice = rex_article_slice::getArticleSlicebyId($slice_id, $clang_id);
            if($slice)
            {
                $slice_priority = (int) $slice->getValue('priority');
                // slice is valid
                if($insertafter > 0)
                {
                    // insertafter is given, let's get it
                    $insertafter_slice = rex_article_slice::getArticleSlicebyId($insertafter, $clang_id);
                    if($insertafter_slice && ($insertafter_slice->getArticleId() == $slice->getArticleId()) && ($insertafter_slice->getCtype() == $slice->getCtype()))
                    {
                        // insertafter_slice exists and is within the same article and is within the same ctype,
                        // let's get its priority
                        $insertafter_prio = (int) $insertafter_slice->getValue('priority');
                    }
                }
                else
                {
                    // insert after is 0 so the new priority is 0
                    $insertafter_prio = 0;
                }

                if($insertafter_prio !== null)
                {
                    // we could define a new priority
                    //
                    $steps = 0;
                    if($direction == 'movedown')
                    {
                        $steps = $insertafter_prio - $slice_priority;
                    }
                    else if($direction == 'moveup')
                    {
                        $steps = $slice_priority - $insertafter_prio - 1;
                    }

                    if($steps > 0)
                    {
                        for($i = 0; $i < $steps; $i++)
                        {
                            // execute the move $step times (the last one is made by rex_content_service::moveSlice itself)
                            $result = parent::execute();
                        }
                    }
                }
            }
        }

        if(empty($result))
        {
            throw new rex_api_exception(bloecks_dragndrop::package()->i18n('something_went_wrong', bloecks_dragndrop::package()->i18n($direction)));
        }

        return $result;
    }
}

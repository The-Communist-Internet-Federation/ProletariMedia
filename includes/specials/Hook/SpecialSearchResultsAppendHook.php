<?php

namespace MediaWiki\Hook;

use MediaWiki\Output\OutputPage;
use MediaWiki\Specials\SpecialSearch;

/**
 * This is a hook handler interface, see docs/Hooks.md.
 * Use the hook name "SpecialSearchResultsAppend" to register handlers implementing this interface.
 *
 * @stable to implement
 * @ingroup Hooks
 */
interface SpecialSearchResultsAppendHook {
	/**
	 * This hook is called immediately before returning HTML on the search results page.
	 *
	 * Useful for including a feedback link.
	 *
	 * @since 1.35
	 *
	 * @param SpecialSearch $specialSearch SpecialSearch object ($this)
	 * @param OutputPage $output $wgOut
	 * @param string $term Search term specified by the user
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onSpecialSearchResultsAppend( $specialSearch, $output, $term );
}

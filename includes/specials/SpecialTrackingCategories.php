<?php
/**
 * Implements Special:TrackingCategories
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @ingroup SpecialPage
 */

use MediaWiki\Cache\LinkBatchFactory;

/**
 * A special page that displays list of tracking categories
 * Tracking categories allow pages with certain characteristics to be tracked.
 * It works by adding any such page to a category automatically.
 * Category is specified by the tracking category's system message.
 *
 * @ingroup SpecialPage
 * @since 1.23
 */

class SpecialTrackingCategories extends SpecialPage {

	/** @var LinkBatchFactory */
	private $linkBatchFactory;

	/** @var TrackingCategories */
	private $trackingCategories;

	/**
	 * @param LinkBatchFactory $linkBatchFactory
	 * @param TrackingCategories $trackingCategories
	 */
	public function __construct(
		LinkBatchFactory $linkBatchFactory,
		TrackingCategories $trackingCategories
	) {
		parent::__construct( 'TrackingCategories' );
		$this->linkBatchFactory = $linkBatchFactory;
		$this->trackingCategories = $trackingCategories;
	}

	public function execute( $par ) {
		$this->setHeaders();
		$this->outputHeader();
		$this->addHelpLink( 'Help:Categories' );
		$this->getOutput()->setPreventClickjacking( false );
		$this->getOutput()->addModuleStyles( [
			'jquery.tablesorter.styles',
			'mediawiki.pager.tablePager'
		] );
		$this->getOutput()->addModules( 'jquery.tablesorter' );
		$this->getOutput()->addHTML(
			Html::openElement( 'table', [ 'class' => 'mw-datatable sortable',
				'id' => 'mw-trackingcategories-table' ] ) . "\n" .
			"<thead><tr>
			<th>" .
				$this->msg( 'trackingcategories-msg' )->escaped() . "
			</th>
			<th>" .
				$this->msg( 'trackingcategories-name' )->escaped() .
			"</th>
			<th>" .
				$this->msg( 'trackingcategories-desc' )->escaped() . "
			</th>
			</tr></thead>"
		);

		$categoryList = $this->trackingCategories->getTrackingCategories();

		$batch = $this->linkBatchFactory->newLinkBatch();
		foreach ( $categoryList as $catMsg => $data ) {
			$batch->addObj( $data['msg'] );
			foreach ( $data['cats'] as $catTitle ) {
				$batch->addObj( $catTitle );
			}
		}
		$batch->execute();

		$this->getHookRunner()->onSpecialTrackingCategories__preprocess( $this, $categoryList );

		$linkRenderer = $this->getLinkRenderer();

		foreach ( $categoryList as $catMsg => $data ) {
			$allMsgs = [];
			$catDesc = $catMsg . '-desc';

			$catMsgTitleText = $linkRenderer->makeLink(
				$data['msg'],
				$catMsg
			);

			foreach ( $data['cats'] as $catTitle ) {
				$html = $linkRenderer->makeLink(
					$catTitle,
					$catTitle->getText()
				);

				$this->getHookRunner()->onSpecialTrackingCategories__generateCatLink(
					$this, $catTitle, $html );

				$allMsgs[] = $html;
			}

			# Extra message, when no category was found
			if ( $allMsgs === [] ) {
				$allMsgs[] = $this->msg( 'trackingcategories-disabled' )->parse();
			}

			/*
			 * Show category description if it exists as a system message
			 * as category-name-desc
			 */
			$descMsg = $this->msg( $catDesc );
			if ( $descMsg->isBlank() ) {
				$descMsg = $this->msg( 'trackingcategories-nodesc' );
			}

			$this->getOutput()->addHTML(
				Html::openElement( 'tr' ) .
				Html::openElement( 'td', [ 'class' => 'mw-trackingcategories-name' ] ) .
					$this->getLanguage()->commaList( array_unique( $allMsgs ) ) .
				Html::closeElement( 'td' ) .
				Html::openElement( 'td', [ 'class' => 'mw-trackingcategories-msg' ] ) .
					$catMsgTitleText .
				Html::closeElement( 'td' ) .
				Html::openElement( 'td', [ 'class' => 'mw-trackingcategories-desc' ] ) .
					$descMsg->parse() .
				Html::closeElement( 'td' ) .
				Html::closeElement( 'tr' )
			);
		}
		$this->getOutput()->addHTML( Html::closeElement( 'table' ) );
	}

	protected function getGroupName() {
		return 'pages';
	}
}

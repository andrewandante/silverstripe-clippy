<% if $CurrentRecord %>
<div id="pages-controller-cms-content" class="has-panel cms-content flexbox-area-grow fill-width fill-height $BaseCSSClasses" data-layout-type="border" data-pjax-fragment="Content" data-ignore-tab-state="true">
    $Tools
	<div class="fill-height flexbox-area-grow">
		<div class="cms-content-header north">
			<div class="cms-content-header-info flexbox-area-grow vertical-align-items">
				<% include SilverStripe\\Admin\\BackLink_Button Backlink=$BreadcrumbsBacklink %>
				<% include SilverStripe\\Admin\\CMSBreadcrumbs %>
			</div>

			<div class="cms-content-header-tabs cms-tabset">
				<ul class="cms-tabset-nav-primary nav nav-tabs">
					<li class="nav-item content-treeview<% if $TabIdentifier == 'edit' %> ui-tabs-active<% end_if %>">
						<a href="$LinkRecordEdit" class="nav-link cms-panel-link" title="Form_EditForm" data-href="$LinkRecordEdit">
							<%t SilverStripe\\CMS\\Controllers\\CMSMain.TabContent 'Content' %>
						</a>
					</li>
					<% if $LinkRecordSettings %>
					<li class="nav-item content-listview<% if $TabIdentifier == 'settings' %> ui-tabs-active<% end_if %>">
						<a href="$LinkRecordSettings" class="nav-link cms-panel-link" title="Form_EditForm" data-href="$LinkRecordSettings">
							<%t SilverStripe\\CMS\\Controllers\\CMSMain.TabSettings 'Settings' %>
						</a>
					</li>
					<% end_if %>
					<li class="nav-item content-listview<% if $TabIdentifier == 'history' %> ui-tabs-active<% end_if %>">
						<a href="$LinkRecordHistory" class="nav-link cms-panel-link" title="Form_EditForm" data-href="$LinkRecordHistory">
							<%t SilverStripe\\CMS\\Controllers\\CMSMain.TabHistory 'History' %>
						</a>
					</li>
                    <% if $HasUserGuides %>
                    <li class="nav-item content-listview<% if $TabIdentifier == 'guide' %> ui-tabs-active<% end_if %>">
                        <a href="$LinkPageUserGuide" class="nav-link cms-panel-link" title="Form_EditForm" data-href="$LinkPageUserGuide">
                            &#128214; User Guides
                        </a>
                    </li>
                    <% end_if %>
				</ul>
			</div>
		</div>

		<div class="flexbox-area-grow fill-height">
			$EditForm
		</div>
	</div>
</div>
<% else %>
<div id="pages-controller-cms-content" class="flexbox-area-grow fill-height cms-content $BaseCSSClasses" data-layout-type="border" data-pjax-fragment="Content">
    <% include SilverStripe\\CMS\\Controllers\\CMSMain_LeftPanel %>
</div>
<% end_if %>

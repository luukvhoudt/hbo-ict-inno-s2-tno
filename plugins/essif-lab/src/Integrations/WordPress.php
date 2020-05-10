<?php

namespace TNO\EssifLab\Integrations;

use TNO\EssifLab\Constants;
use TNO\EssifLab\Integrations\Contracts\BaseIntegration;
use TNO\EssifLab\Models\Contracts\Model;
use TNO\EssifLab\Views\Items\MultiDimensional;
use TNO\EssifLab\Views\TypeList;

class WordPress extends BaseIntegration {
	const ADD_ACTION = 'add_action';

	const ADD_MENU_PAGE = 'add_menu_page';

	const DEFAULT_TYPE_ARGS = [
		'public' => false,
		'show_ui' => true,
	];

	const ADMIN_MENU_CAPABILITY = 'manage_options';

	const ADMIN_MENU_ICON_URL = 'dashicons-lock';

	protected $utilities = [
		BaseIntegration::REGISTER_TYPE => 'register_post_type',
		BaseIntegration::REGISTER_RELATION => 'add_meta_box',
		BaseIntegration::GET_ADD_TYPE_LINK => self::class.'::getAddTypeLink',
		BaseIntegration::GET_EDIT_TYPE_LINK => self::class.'::getEditTypeLink',
		self::ADD_ACTION => self::ADD_ACTION,
		self::ADD_MENU_PAGE => self::ADD_MENU_PAGE,
	];

	function install(): void {
		$this->useUtility(self::ADD_ACTION, 'admin_menu', [$this, 'registerAdminMenu']);
		$this->useUtility(self::ADD_ACTION, 'init', [$this, 'registerPostTypes']);
		$this->registerMetaBoxes();
	}

	function registerAdminMenu(): void {
		$page_title = $this->application->getName();
		$menu_title = $page_title;
		$capability = self::ADMIN_MENU_CAPABILITY;
		$menu_slug = $this->application->getNamespace();
		$icon_url = self::ADMIN_MENU_ICON_URL;
		$callback = null;
		$this->useUtility(self::ADD_MENU_PAGE, $page_title, $menu_title, $capability, $menu_slug, $callback, $icon_url);
	}

	function registerPostTypes(): void {
		BaseIntegration::forAllModels(function (Model $instance) {
			$this->registerModelType($instance);
		});
	}

	function registerModelType(Model $model): void {
		$args = $this->parseTypeArgs($model);
		$this->useUtility(BaseIntegration::REGISTER_TYPE, $model->getTypeName(), $args);
	}

	function registerMetaBoxes(): void {
		BaseIntegration::forAllModels(function (Model $model) {
			$hook = 'add_meta_boxes_'.$model->getTypeName();
			$this->useUtility(self::ADD_ACTION, $hook, function () use ($model) {
				$this->registerModelRelations($model);
			});
		});
	}

	function registerModelRelations(Model $model): void {
		$classes = $model->getRelations();
		BaseIntegration::forEachModel($classes, function (Model $related) use ($model) {
			$id = $model->getTypeName().'_'.$related->getTypeName();
			$title = self::toTitleCase($related->getPluralName());
			$callback = function () use ($model, $related) {
				print $this->renderModelRelation($model, $related);
			};
			$screen = $model->getTypeName();
			$context = 'normal';
			$this->useUtility(BaseIntegration::REGISTER_RELATION, $id, $title, $callback, $screen, $context);
		});
	}

	function renderModelRelation(Model $parent, Model $related): string {
		$values = [
			new MultiDimensional([], TypeList::FORM_ITEMS),
			new MultiDimensional([], TypeList::LIST_ITEMS),
		];

		return (new TypeList($this, $related, $values))->render();
	}

	static function getAddTypeLink(string $postType) {
		return get_admin_url(null, 'edit.php?post_type='.$postType);
	}

	static function getEditTypeLink(int $id) {
		return get_edit_post_link($id);
	}

	static function generateLabels(Model $model): array {
		$singular = $model->getSingularName();
		$singularTitleCase = self::toTitleCase($singular);
		$plural = $model->getPluralName();
		$pluralTitleCase = self::toTitleCase($plural);

		return [
			'name' => $pluralTitleCase,
			'singular_name' => $singularTitleCase,
			'menu_name' => $pluralTitleCase,
			'name_admin_bar' => $singularTitleCase,
			'archives' => sprintf('%s Archives', $singularTitleCase),
			'attributes' => sprintf('%s Attributes', $singularTitleCase),
			'parent_item_colon' => sprintf('Parent %s:', $singularTitleCase),
			'all_items' => $pluralTitleCase,
			'add_new_item' => sprintf('Add New %s', $singularTitleCase),
			'new_item' => sprintf('New %s', $singularTitleCase),
			'edit_item' => sprintf('Edit %s', $singularTitleCase),
			'update_item' => sprintf('Update %s', $singularTitleCase),
			'view_item' => sprintf('View %s', $singularTitleCase),
			'view_items' => sprintf('View %s', $pluralTitleCase),
			'search_items' => sprintf('Search %s', $pluralTitleCase),
			'not_found' => sprintf('No %s found', $plural),
			'not_found_in_trash' => sprintf('Not %s found in Trash', $plural),
			'insert_into_item' => sprintf('Insert into %s', $singular),
			'uploaded_to_this_item' => sprintf('Uploaded to this %s', $singular),
			'items_list' => sprintf('%s list', $pluralTitleCase),
			'items_list_navigation' => sprintf('%s list navigation', $pluralTitleCase),
			'filter_items_list' => sprintf('Filter %s list', $plural),
		];
	}

	static function toTitleCase(string $v): string {
		return implode(' ', array_map('ucfirst', explode(' ', $v)));
	}

	private function parseTypeArgs(Model $model): array {
		$default = array_merge(self::DEFAULT_TYPE_ARGS, [
			'labels' => self::generateLabels($model),
			'show_in_menu' => $this->application->getNamespace(),
			'supports' => $model->getFields(),
		]);

		$args = $model->getTypeArgs();
		if (array_key_exists(Constants::TYPE_ARG_HIDE_FROM_NAV, $args)) {
			$parsed['show_ui'] = $args[Constants::TYPE_ARG_HIDE_FROM_NAV];
			unset($args[Constants::TYPE_ARG_HIDE_FROM_NAV]);
		}

		return array_merge($default, $args);
	}
}
<?php
/**
 * Class SlideshowPluginSlideshowSettingsHandler handles all database/settings interactions for the slideshows.
 *
 * @since 2.1.20
 * @author Stefan Boonstra
 */
class SlideshowPluginSlideshowSettingsHandler
{
	/** @var string $nonceAction */
	static $nonceAction = 'slideshow-jquery-image-gallery-nonceAction';
	/** @var string $nonceName */
	static $nonceName = 'slideshow-jquery-image-gallery-nonceName';

	/** @var string $settingsKey */
	static $settingsKey = 'settings';
	/** @var string $styleSettingsKey */
	static $styleSettingsKey = 'styleSettings';
	/** @var string $slidesKey */
	static $slidesKey = 'slides';

	/** @var array $settings      Used for caching by slideshow ID */
	static $settings = array();
	/** @var array $styleSettings Used for caching by slideshow ID */
	static $styleSettings = array();
	/** @var array $slides        Used for caching by slideshow ID */
	static $slides = array();

	/**
	 * Returns all settings that belong to the passed post ID retrieved from
	 * database, merged with default values from getDefaults(). Does not merge
	 * if mergeDefaults is false.
	 *
	 * If all data (including field information and description) is needed,
	 * set fullDefinition to true. See getDefaults() documentation for returned
	 * values. mergeDefaults must be true for this option to have any effect.
	 *
	 * If enableCache is set to true, results are saved into local storage for
	 * more efficient use. If data was already stored, cached data will be
	 * returned, unless $enableCache is set to false. Settings will not be
	 * cached.
	 *
	 * @since 2.1.20
	 * @param int $slideshowId
	 * @param boolean $fullDefinition (optional, defaults to false)
	 * @param boolean $enableCache (optional, defaults to true)
	 * @param boolean $mergeDefaults (optional, defaults to true)
	 * @return mixed $settings
	 */
	static function getAllSettings($slideshowId, $fullDefinition = false, $enableCache = true, $mergeDefaults = true)
	{
		$settings                          = array();
		$settings[self::$settingsKey]      = self::getSettings($slideshowId, $fullDefinition, $enableCache,  $mergeDefaults);
		$settings[self::$styleSettingsKey] = self::getStyleSettings($slideshowId, $fullDefinition, $enableCache,  $mergeDefaults);
		$settings[self::$slidesKey]        = self::getSlides($slideshowId, $enableCache);

		return $settings;
	}

	/**
	 * Returns settings retrieved from database.
	 *
	 * For a full description of the parameters, see getAllSettings().
	 *
	 * @since 2.1.20
	 * @param int $slideshowId
	 * @param boolean $fullDefinition (optional, defaults to false)
	 * @param boolean $enableCache (optional, defaults to true)
	 * @param boolean $mergeDefaults (optional, defaults to true)
	 * @return mixed $settings
	 */
	static function getSettings($slideshowId, $fullDefinition = false, $enableCache = true, $mergeDefaults = true)
	{
		if (!is_numeric($slideshowId) ||
			empty($slideshowId))
		{
			return array();
		}

		// Set caching to false and merging defaults to true when $fullDefinition is set to true
		if ($fullDefinition)
		{
			$enableCache   = false;
			$mergeDefaults = true;
		}

		// If no cache is set, or cache is disabled
		if (!isset(self::$settings[$slideshowId]) ||
			empty(self::$settings[$slideshowId]) ||
			!$enableCache)
		{
			// Meta data
			$settingsMeta = get_post_meta(
				$slideshowId,
				self::$settingsKey,
				true
			);

			if (!$settingsMeta ||
				!is_array($settingsMeta))
			{
				$settingsMeta = array();
			}

			// If the settings should be merged with the defaults as a full definition, place each setting in an array referenced by 'value'.
			if ($fullDefinition)
			{
				foreach ($settingsMeta as $key => $value)
				{
					$settingsMeta[$key] = array('value' => $value);
				}
			}

			// Get defaults
			$defaults = array();

			if ($mergeDefaults)
			{
				$defaults = self::getDefaultSettings($fullDefinition);
			}

			// Merge with defaults, recursively if a the full definition is required
			if ($fullDefinition)
			{
				$settings = array_merge_recursive(
					$defaults,
					$settingsMeta
				);
			}
			else
			{
				$settings = array_merge(
					$defaults,
					$settingsMeta
				);
			}

			// Cache if cache is enabled
			if ($enableCache)
			{
				self::$settings[$slideshowId] = $settings;
			}
		}
		else
		{
			// Get cached settings
			$settings = self::$settings[$slideshowId];
		}

		// Return
		return $settings;
	}

	/**
	 * Returns style settings retrieved from database.
	 *
	 * For a full description of the parameters, see getAllSettings().
	 *
	 * @since 2.1.20
	 * @param int $slideshowId
	 * @param boolean $fullDefinition (optional, defaults to false)
	 * @param boolean $enableCache (optional, defaults to true)
	 * @param boolean $mergeDefaults (optional, defaults to true)
	 * @return mixed $settings
	 */
	static function getStyleSettings($slideshowId, $fullDefinition = false, $enableCache = true, $mergeDefaults = true)
	{
		if (!is_numeric($slideshowId) ||
			empty($slideshowId))
		{
			return array();
		}

		// Set caching to false and merging defaults to true when $fullDefinition is set to true
		if ($fullDefinition)
		{
			$enableCache   = false;
			$mergeDefaults = true;
		}

		// If no cache is set, or cache is disabled
		if (!isset(self::$styleSettings[$slideshowId]) ||
			empty(self::$styleSettings[$slideshowId]) ||
			!$enableCache)
		{
			// Meta data
			$styleSettingsMeta = get_post_meta(
				$slideshowId,
				self::$styleSettingsKey,
				true
			);

			if (!$styleSettingsMeta ||
				!is_array($styleSettingsMeta))
			{
				$styleSettingsMeta = array();
			}

			// If the settings should be merged with the defaults as a full definition, place each setting in an array referenced by 'value'.
			if ($fullDefinition)
			{
				foreach ($styleSettingsMeta as $key => $value)
				{
					$styleSettingsMeta[$key] = array('value' => $value);
				}
			}

			// Get defaults
			$defaults = array();

			if ($mergeDefaults)
			{
				$defaults = self::getDefaultStyleSettings($fullDefinition);
			}

			// Merge with defaults, recursively if a the full definition is required
			if ($fullDefinition)
			{
				$styleSettings = array_merge_recursive(
					$defaults,
					$styleSettingsMeta
				);
			}
			else
			{
				$styleSettings = array_merge(
					$defaults,
					$styleSettingsMeta
				);
			}

			// Cache if cache is enabled
			if ($enableCache)
			{
				self::$styleSettings[$slideshowId] = $styleSettings;
			}
		}
		else
		{
			// Get cached settings
			$styleSettings = self::$styleSettings[$slideshowId];
		}

		// Return
		return $styleSettings;
	}

	/**
	 * Returns slides retrieved from database.
	 *
	 * For a full description of the parameters, see getAllSettings().
	 *
	 * @since 2.1.20
	 * @param int $slideshowId
	 * @param boolean $enableCache (optional, defaults to true)
	 * @return mixed $settings
	 */
	static function getSlides($slideshowId, $enableCache = true)
	{
		if (!is_numeric($slideshowId) ||
			empty($slideshowId))
		{
			return array();
		}

		// If no cache is set, or cache is disabled
		if (!isset(self::$slides[$slideshowId]) ||
			empty(self::$slides[$slideshowId]) ||
			!$enableCache)
		{
			// Meta data
			$slides = get_post_meta(
				$slideshowId,
				self::$slidesKey,
				true
			);
		}
		else
		{
			// Get cached settings
			$slides = self::$slides[$slideshowId];
		}

		// XTEC ************ AFEGIT - Get slides from picasa or google photos or parse album extension content
		// 2014.10.22 @jmeler && @frncesc - 2015.12.21 @nacho && @aginard - 2016.04.22 @sarjona

		// Get photos from album extension list or mosaic
		$album_extension = get_post_meta($slideshowId, 'album_extension', true);
		if ($album_extension) {
			foreach(preg_split("/((\r?\n)|(\r\n?))/", $album_extension) as $entry){
				$entry = trim($entry);
				if (!empty($entry)) {
					if (strpos($entry, 'http') === 0) {
						// List (Image URL)
						$slides[] = array(
							"url"       => $entry,
							"urlTarget" => $entry,
							"type"      => "image",
						);
					} else if (strpos($entry, '<a href') === 0) {
						// Mosaic (HTML code)
						$DOM = new DOMDocument;
						// set error level
						$internalErrors = libxml_use_internal_errors(true);
						// load HTML
						$DOM->loadHTML($entry);
						// Restore error level
						libxml_use_internal_errors($internalErrors);

						$items = $DOM->getElementsByTagName('a');
						foreach ($items as $item) {
							$url = $item->getAttributeNode('href')->nodeValue;
						}
						$items = $DOM->getElementsByTagName('img');
						foreach ($items as $item) {
							$img = $item->getAttributeNode('src')->nodeValue;
						}
						$slides[] = array(
							"url"       => $img,
							"urlTarget" => $url,
							"type"      => "image",
						);

					}
				}
			}
		}

		// Get photos from Picasa or GooglePhotos URL
		$picasa_album = get_post_meta($slideshowId,"picasa_album",true);
		$googlephotos_album = get_post_meta($slideshowId,"googlephotos_album",true);

		$albums_json=array();

		if ($picasa_album){
			$pos = strpos($picasa_album, 'data/feed/base');
			if ($pos !== false) {
				// RSS https://picasaweb.google.com/data/feed/base/user/USER_ID/albumid/ALBUM_ID?alt=rss&kind=photo&hl=ca
				$extra_params = "&alt=json&imgmax=1024&fields=entry(content,media%3Agroup(media%3Adescription),link[%40rel%3D%27alternate%27](%40href))";
				$albums_json[] = str_replace("alt=rss", "", $picasa_album) . $extra_params;
			}else {
				// URL https://picasaweb.google.com/USER_ID/ALBUM_NAME
				$extra_params = '?alt=json&imgmax=1024&fields=entry(content,media%3Agroup(media%3Adescription),link[%40rel%3D%27alternate%27](%40href))';

				$parsed_url = parse_url($picasa_album);
				$params = explode('/', $parsed_url['path']);

				$albums_json[] = $parsed_url['scheme'] . '://' . $parsed_url['host'] . '/data/feed/api/user/' . $params[1] . '/album/' . $params[2] . '/' . $extra_params;
			}
		}
		if ($googlephotos_album) {
			preg_match_all('/.*plus.google.com.*photos\/(\d*)\/album.*\/(\d*)/i',$googlephotos_album, $result);
			$googlephotos_feed = "http://photos.googleapis.com/data/feed/api/user/" . $result[1][0] . "/albumid/" . $result[2][0];
			$extra_params = "?alt=json&imgmax=1024&fields=entry(content,media%3Agroup(media%3Adescription),link[%40rel%3D%27alternate%27](%40href))";
			$albums_json[] = $googlephotos_feed . $extra_params;
		}

		foreach ($albums_json as $album_json) {
			$request = new WP_Http;
			$result = $request->request($album_json);

			if (!is_wp_error($result)) {
				$album = json_decode($result['body'],true);
				if ($album) {
					foreach($album['feed']['entry'] as $item){
						$slides[] = array(
							"title"     => $item['media$group']['media$description']['$t'],
							"url"       => $item['content']['src'],
							"urlTarget" => $item['link'][0]['href'],
							"type"      => "image",
						);
					}
				} else {
					echo __("Album not available. Album ID not valid?",'slideshow-jquery-image-gallery');
				}
			} else {
				echo __("Timeout error. Album not available. Try to refresh this page",'slideshow-jquery-image-gallery');
			}
		}
//************ FI

		// Sort slides by order ID
		if (is_array($slides))
		{
			ksort($slides);
		}
		else
		{
			$slides = array();
		}

		// Return
		return array_values($slides);
	}

	/**
	 * Get new settings from $_POST variable and merge them with
	 * the old and default settings.
	 *
	 * @since 2.1.20
	 * @param int $postId
	 * @return int $postId
	 */
	static function save($postId)
	{
		// Verify nonce, check if user has sufficient rights and return on auto-save.
		if (get_post_type($postId) != SlideshowPluginPostType::$postType ||
			(!isset($_POST[self::$nonceName]) || !wp_verify_nonce($_POST[self::$nonceName], self::$nonceAction)) ||
			!current_user_can('slideshow-jquery-image-gallery-edit-slideshows', $postId) ||
			(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE))
		{
			return $postId;
		}

		// Old settings
		$oldSettings      = self::getSettings($postId);
		$oldStyleSettings = self::getStyleSettings($postId);

		// Get new settings from $_POST, making sure they're arrays
		$newPostSettings = $newPostStyleSettings = $newPostSlides = array();

		if (isset($_POST[self::$settingsKey]) &&
			is_array($_POST[self::$settingsKey]))
		{
			$newPostSettings = $_POST[self::$settingsKey];
		}

		if (isset($_POST[self::$styleSettingsKey]) &&
			is_array($_POST[self::$styleSettingsKey]))
		{
			$newPostStyleSettings = $_POST[self::$styleSettingsKey];
		}

		if (isset($_POST[self::$slidesKey]) &&
			is_array($_POST[self::$slidesKey]))
		{
			$newPostSlides = $_POST[self::$slidesKey];
		}

		// Merge new settings with its old values
		$newSettings = array_merge(
			$oldSettings,
			$newPostSettings
		);

		// Merge new style settings with its old values
		$newStyleSettings = array_merge(
			$oldStyleSettings,
			$newPostStyleSettings
		);

		// XTEC ************ AFEGIT - save external albums addr
		// 2014.10.22 @jmeler - 2016.04.22 @sarjona
		$album_extension = isset($_POST["album_extension"])?$_POST["album_extension"]:'';
		// Only save textarea information if is valid
		if (empty($album_extension) || strpos($album_extension, 'http') === 0 || strpos($album_extension, '<a href') === 0) {
			update_post_meta($postId, "album_extension", $album_extension);
		}
		$picasa_album = isset($_POST["picasa_album"])?$_POST["picasa_album"]:'';
		$googlephotos_album = isset($_POST["googlephotos_album"])?$_POST["googlephotos_album"]:'';
		update_post_meta($postId, "picasa_album", $picasa_album);
		update_post_meta($postId, "googlephotos_album", $googlephotos_album);
		//************ FI

		// Save settings
		update_post_meta($postId, self::$settingsKey, $newSettings);
		update_post_meta($postId, self::$styleSettingsKey, $newStyleSettings);
		update_post_meta($postId, self::$slidesKey, $newPostSlides);

		// Return
		return $postId;
	}

	/**
	 * Returns an array of all defaults. The array will be returned
	 * like this:
	 * array([settingsKey] => array([settingName] => [settingValue]))
	 *
	 * If all default data (including field information and description)
	 * is needed, set fullDefinition to true. Data in the full definition is
	 * build up as follows:
	 * array([settingsKey] => array([settingName] => array('type' => [inputType], 'value' => [value], 'default' => [default], 'description' => [description], 'options' => array([options]), 'dependsOn' => array([dependsOn], [onValue]), 'group' => [groupName])))
	 *
	 * Finally, when you require the defaults as they were programmed in,
	 * set this parameter to false. When set to true, the database will
	 * first be consulted for user-customized defaults. Defaults to true.
	 *
	 * @since 2.1.20
	 * @param mixed $key (optional, defaults to null, getting all keys)
	 * @param boolean $fullDefinition (optional, defaults to false)
	 * @param boolean $fromDatabase (optional, defaults to true)
	 * @return mixed $data
	 */
	static function getAllDefaults($key = null, $fullDefinition = false, $fromDatabase = true)
	{
		$data                          = array();
		$data[self::$settingsKey]      = self::getDefaultSettings($fullDefinition, $fromDatabase);
		$data[self::$styleSettingsKey] = self::getDefaultStyleSettings($fullDefinition, $fromDatabase);

		return $data;
	}

	/**
	 * Returns an array of setting defaults.
	 *
	 * For a full description of the parameters, see getAllDefaults().
	 *
	 * @since 2.1.20
	 * @param boolean $fullDefinition (optional, defaults to false)
	 * @param boolean $fromDatabase (optional, defaults to true)
	 * @return mixed $data
	 */
	static function getDefaultSettings($fullDefinition = false, $fromDatabase = true)
	{
		// Much used data for translation
		$yes = __('Yes', 'slideshow-jquery-image-gallery');
		$no  = __('No', 'slideshow-jquery-image-gallery');

		// XTEC ************ MODIFICAT - Change default settings
		// 2014.11.20 @jmeler

		// Default values
		$data = array(
			'animation' => 'slide',
			'slideSpeed' => '1',
			'descriptionSpeed' => '0.4',
			'intervalSpeed' => '8',
			'slidesPerView' => '1',
			'maxWidth' => '0',
			'aspectRatio' => '3:1',
			'height' => '300',
			'imageBehaviour' => 'crop',
			'showDescription' => 'true',
			'hideDescription' => 'true',
			'preserveSlideshowDimensions' => 'false',
			'enableResponsiveness' => 'true',
			'play' => 'false',
			'loop' => 'true',
			'pauseOnHover' => 'true',
			'controllable' => 'true',
			'hideNavigationButtons' => 'true',
			'showPagination' => 'true',
			'hidePagination' => 'true',
			'controlPanel' => 'true',
			'hideControlPanel' => 'true',
			'waitUntilLoaded' => 'true',
			'showLoadingIcon' => 'true',
			'random' => 'false',
			'avoidFilter' => 'true'
		);

		//************ ORIGINAL
		/*
		// Default values
		$data = array(
			'animation' => 'slide',
			'slideSpeed' => '1',
			'descriptionSpeed' => '0.4',
			'intervalSpeed' => '5',
			'slidesPerView' => '1',
			'maxWidth' => '0',
			'aspectRatio' => '3:1',
			'height' => '200',
			'imageBehaviour' => 'natural',
			'showDescription' => 'true',
			'hideDescription' => 'true',
			'preserveSlideshowDimensions' => 'false',
			'enableResponsiveness' => 'true',
			'play' => 'true',
			'loop' => 'true',
			'pauseOnHover' => 'true',
			'controllable' => 'true',
			'hideNavigationButtons' => 'false',
			'showPagination' => 'true',
			'hidePagination' => 'true',
			'controlPanel' => 'false',
			'hideControlPanel' => 'true',
			'waitUntilLoaded' => 'true',
			'showLoadingIcon' => 'true',
			'random' => 'false',
			'avoidFilter' => 'true'
		);
		*/
		//************ FI

		// Read defaults from database and merge with $data, when $fromDatabase is set to true
		if ($fromDatabase)
		{
			$data = array_merge(
				$data,
				$customData = get_option(SlideshowPluginGeneralSettings::$defaultSettings, array())
			);
		}

		// Full definition
		if ($fullDefinition)
		{

			$descriptions = array(
					'animation'                   => __('Animation used for transition between slides', 'slideshow-jquery-image-gallery'),
					'slideSpeed'                  => __('Number of seconds the slide takes to slide in', 'slideshow-jquery-image-gallery'),
					'descriptionSpeed'            => __('Number of seconds the description takes to slide in', 'slideshow-jquery-image-gallery'),
					'intervalSpeed'               => __('Seconds between changing slides', 'slideshow-jquery-image-gallery'),
					'slidesPerView'               => __('Number of slides to fit into one slide', 'slideshow-jquery-image-gallery'),
					'maxWidth'                    => __('Maximum width. When maximum width is 0, maximum width is ignored', 'slideshow-jquery-image-gallery'),
					// XTEC ************ MODIFICAT - Delete Wikipedia link
					// 2015.04.30 @jmeler
					'aspectRatio'                 => __('Proportional relationship%s between slideshow\'s width and height (width:height)', 'slideshow-jquery-image-gallery'),
					// ORIGINAL
					// 'aspectRatio'                 => sprintf('<a href="' . str_replace('%', '%%', __('http://en.wikipedia.org/wiki/Aspect_ratio_(image)', 'slideshow-jquery-image-gallery')) . '" title="' . __('More info', 'slideshow-jquery-image-gallery') . '" target="_blank">' . __('Proportional relationship%s between slideshow\'s width and height (width:height)', 'slideshow-jquery-image-gallery'), '</a>'),
					// FI
					'height'                      => __('Slideshow\'s height', 'slideshow-jquery-image-gallery'),
					'imageBehaviour'              => __('Image behaviour', 'slideshow-jquery-image-gallery'),
					'preserveSlideshowDimensions' => __('Shrink slideshow\'s height when width shrinks', 'slideshow-jquery-image-gallery'),
					'enableResponsiveness'        => __('Enable responsiveness (Shrink slideshow\'s width when page\'s width shrinks)', 'slideshow-jquery-image-gallery'),
					'showDescription'             => __('Show title and description', 'slideshow-jquery-image-gallery'),
					'hideDescription'             => __('Hide description box, pop up when mouse hovers over', 'slideshow-jquery-image-gallery'),
					'play'                        => __('Automatically slide to the next slide', 'slideshow-jquery-image-gallery'),
					'loop'                        => __('Return to the beginning of the slideshow after last slide', 'slideshow-jquery-image-gallery'),
					'pauseOnHover'                => __('Pause slideshow when mouse hovers over', 'slideshow-jquery-image-gallery'),
					'controllable'                => __('Activate navigation buttons', 'slideshow-jquery-image-gallery'),
					'hideNavigationButtons'       => __('Hide navigation buttons, show when mouse hovers over', 'slideshow-jquery-image-gallery'),
					'showPagination'              => __('Activate pagination', 'slideshow-jquery-image-gallery'),
					'hidePagination'              => __('Hide pagination, show when mouse hovers over', 'slideshow-jquery-image-gallery'),
					'controlPanel'                => __('Activate control panel (play and pause button)', 'slideshow-jquery-image-gallery'),
					'hideControlPanel'            => __('Hide control panel, show when mouse hovers over', 'slideshow-jquery-image-gallery'),
					'waitUntilLoaded'             => __('Wait until the next slide has loaded before showing it', 'slideshow-jquery-image-gallery'),
					'showLoadingIcon'             => __('Show a loading icon until the first slide appears', 'slideshow-jquery-image-gallery'),
					'random'                      => __('Randomize slides', 'slideshow-jquery-image-gallery'),
					'avoidFilter'                 => sprintf(__('Avoid content filter (disable if \'%s\' is shown)', 'slideshow-jquery-image-gallery'), SlideshowPluginShortcode::$bookmark)
			);

			// XTEC ************ MODIFICAT - Change order for usability
			// 2015.04.30 @jmeler
			$data = array(
					'animation'                   => array('type' => 'select', 'default' => $data['animation']                  , 'description' => $descriptions['animation']                  , 'group' => __('Animation', 'slideshow-jquery-image-gallery')    , 'options' => array('slide' => __('Slide Left', 'slideshow-jquery-image-gallery'), 'slideRight' => __('Slide Right', 'slideshow-jquery-image-gallery'), 'slideUp' => __('Slide Up', 'slideshow-jquery-image-gallery'), 'slideDown' => __('Slide Down', 'slideshow-jquery-image-gallery'), 'crossFade' => __('Cross Fade', 'slideshow-jquery-image-gallery'), 'directFade' => __('Direct Fade', 'slideshow-jquery-image-gallery'), 'fade' => __('Fade', 'slideshow-jquery-image-gallery'), 'random' => __('Random Animation', 'slideshow-jquery-image-gallery'))),
					'slideSpeed'                  => array('type' => 'text'  , 'default' => $data['slideSpeed']                 , 'description' => $descriptions['slideSpeed']                 , 'group' => __('Animation', 'slideshow-jquery-image-gallery')),
					'descriptionSpeed'            => array('type' => 'text'  , 'default' => $data['descriptionSpeed']           , 'description' => $descriptions['descriptionSpeed']           , 'group' => __('Animation', 'slideshow-jquery-image-gallery')),
					'intervalSpeed'               => array('type' => 'text'  , 'default' => $data['intervalSpeed']              , 'description' => $descriptions['intervalSpeed']              , 'group' => __('Animation', 'slideshow-jquery-image-gallery')),
					'slidesPerView'               => array('type' => 'text'  , 'default' => $data['slidesPerView']              , 'description' => $descriptions['slidesPerView']              , 'group' => __('Display', 'slideshow-jquery-image-gallery')),
					'maxWidth'                    => array('type' => 'text'  , 'default' => $data['maxWidth']                   , 'description' => $descriptions['maxWidth']                   , 'group' => __('Display', 'slideshow-jquery-image-gallery')),
					'enableResponsiveness'        => array('type' => 'radio' , 'default' => $data['enableResponsiveness']       , 'description' => $descriptions['enableResponsiveness']       , 'group' => __('Display', 'slideshow-jquery-image-gallery')      , 'options' => array('true' => $yes, 'false' => $no)),
					'preserveSlideshowDimensions' => array('type' => 'radio' , 'default' => $data['preserveSlideshowDimensions'], 'description' => $descriptions['preserveSlideshowDimensions'], 'group' => __('Display', 'slideshow-jquery-image-gallery')      , 'options' => array('true' => $yes, 'false' => $no) , 'dependsOn' => array('settings[enableResponsiveness]', 'true')),
					'aspectRatio'                 => array('type' => 'text'  , 'default' => $data['aspectRatio']                , 'description' => $descriptions['aspectRatio']                , 'group' => __('Display', 'slideshow-jquery-image-gallery')                                                           , 'dependsOn' => array('settings[preserveSlideshowDimensions]', 'true')),
					'height'                      => array('type' => 'text'  , 'default' => $data['height']                     , 'description' => $descriptions['height']                     , 'group' => __('Display', 'slideshow-jquery-image-gallery')                                                           , 'dependsOn' => array('settings[preserveSlideshowDimensions]', 'false')),
					'imageBehaviour'              => array('type' => 'select', 'default' => $data['imageBehaviour']             , 'description' => $descriptions['imageBehaviour']             , 'group' => __('Display', 'slideshow-jquery-image-gallery')      , 'options' => array('natural' => __('Natural and centered', 'slideshow-jquery-image-gallery'), 'crop' => __('Crop to fit', 'slideshow-jquery-image-gallery'), 'stretch' => __('Stretch to fit', 'slideshow-jquery-image-gallery'))),
					'showDescription'             => array('type' => 'radio' , 'default' => $data['showDescription']            , 'description' => $descriptions['showDescription']            , 'group' => __('Display', 'slideshow-jquery-image-gallery')      , 'options' => array('true' => $yes, 'false' => $no)),
					'hideDescription'             => array('type' => 'radio' , 'default' => $data['hideDescription']            , 'description' => $descriptions['hideDescription']            , 'group' => __('Display', 'slideshow-jquery-image-gallery')      , 'options' => array('true' => $yes, 'false' => $no) , 'dependsOn' => array('settings[showDescription]', 'true')),
					'play'                        => array('type' => 'radio' , 'default' => $data['play']                       , 'description' => $descriptions['play']                       , 'group' => __('Control', 'slideshow-jquery-image-gallery')      , 'options' => array('true' => $yes, 'false' => $no)),
					'loop'                        => array('type' => 'radio' , 'default' => $data['loop']                       , 'description' => $descriptions['loop']                       , 'group' => __('Control', 'slideshow-jquery-image-gallery')      , 'options' => array('true' => $yes, 'false' => $no)),
					'pauseOnHover'                => array('type' => 'radio' , 'default' => $data['loop']                       , 'description' => $descriptions['pauseOnHover']               , 'group' => __('Control', 'slideshow-jquery-image-gallery')      , 'options' => array('true' => $yes, 'false' => $no)),
					'controllable'                => array('type' => 'radio' , 'default' => $data['controllable']               , 'description' => $descriptions['controllable']               , 'group' => __('Control', 'slideshow-jquery-image-gallery')      , 'options' => array('true' => $yes, 'false' => $no)),
					'hideNavigationButtons'       => array('type' => 'radio' , 'default' => $data['hideNavigationButtons']      , 'description' => $descriptions['hideNavigationButtons']      , 'group' => __('Control', 'slideshow-jquery-image-gallery')      , 'options' => array('true' => $yes, 'false' => $no) , 'dependsOn' => array('settings[controllable]', 'true')),
					'showPagination'              => array('type' => 'radio' , 'default' => $data['showPagination']             , 'description' => $descriptions['showPagination']             , 'group' => __('Control', 'slideshow-jquery-image-gallery')      , 'options' => array('true' => $yes, 'false' => $no)),
					'hidePagination'              => array('type' => 'radio' , 'default' => $data['hidePagination']             , 'description' => $descriptions['hidePagination']             , 'group' => __('Control', 'slideshow-jquery-image-gallery')      , 'options' => array('true' => $yes, 'false' => $no) , 'dependsOn' => array('settings[showPagination]', 'true')),
					'controlPanel'                => array('type' => 'radio' , 'default' => $data['controlPanel']               , 'description' => $descriptions['controlPanel']               , 'group' => __('Control', 'slideshow-jquery-image-gallery')      , 'options' => array('true' => $yes, 'false' => $no)),
					'hideControlPanel'            => array('type' => 'radio' , 'default' => $data['hideControlPanel']           , 'description' => $descriptions['hideControlPanel']           , 'group' => __('Control', 'slideshow-jquery-image-gallery')      , 'options' => array('true' => $yes, 'false' => $no) , 'dependsOn' => array('settings[controlPanel]', 'true')),
					'waitUntilLoaded'             => array('type' => 'radio' , 'default' => $data['waitUntilLoaded']            , 'description' => $descriptions['waitUntilLoaded']            , 'group' => __('Miscellaneous', 'slideshow-jquery-image-gallery'), 'options' => array('true' => $yes, 'false' => $no)),
					'showLoadingIcon'             => array('type' => 'radio' , 'default' => $data['showLoadingIcon']            , 'description' => $descriptions['showLoadingIcon']            , 'group' => __('Miscellaneous', 'slideshow-jquery-image-gallery'), 'options' => array('true' => $yes, 'false' => $no) , 'dependsOn' => array('settings[waitUntilLoaded]', 'true')),
					'random'                      => array('type' => 'radio' , 'default' => $data['random']                     , 'description' => $descriptions['random']                     , 'group' => __('Miscellaneous', 'slideshow-jquery-image-gallery'), 'options' => array('true' => $yes, 'false' => $no)),
					'avoidFilter'                 => array('type' => 'radio' , 'default' => $data['avoidFilter']                , 'description' => $descriptions['avoidFilter']                , 'group' => __('Miscellaneous', 'slideshow-jquery-image-gallery'), 'options' => array('true' => $yes, 'false' => $no))
			);
			// ORIGINAL
			/*
			$data = array(
				'animation'                   => array('type' => 'select', 'default' => $data['animation']                  , 'description' => $descriptions['animation']                  , 'group' => __('Animation', 'slideshow-jquery-image-gallery')    , 'options' => array('slide' => __('Slide Left', 'slideshow-jquery-image-gallery'), 'slideRight' => __('Slide Right', 'slideshow-jquery-image-gallery'), 'slideUp' => __('Slide Up', 'slideshow-jquery-image-gallery'), 'slideDown' => __('Slide Down', 'slideshow-jquery-image-gallery'), 'crossFade' => __('Cross Fade', 'slideshow-jquery-image-gallery'), 'directFade' => __('Direct Fade', 'slideshow-jquery-image-gallery'), 'fade' => __('Fade', 'slideshow-jquery-image-gallery'), 'random' => __('Random Animation', 'slideshow-jquery-image-gallery'))),
				'slideSpeed'                  => array('type' => 'text'  , 'default' => $data['slideSpeed']                 , 'description' => $descriptions['slideSpeed']                 , 'group' => __('Animation', 'slideshow-jquery-image-gallery')),
				'descriptionSpeed'            => array('type' => 'text'  , 'default' => $data['descriptionSpeed']           , 'description' => $descriptions['descriptionSpeed']           , 'group' => __('Animation', 'slideshow-jquery-image-gallery')),
				'intervalSpeed'               => array('type' => 'text'  , 'default' => $data['intervalSpeed']              , 'description' => $descriptions['intervalSpeed']              , 'group' => __('Animation', 'slideshow-jquery-image-gallery')),
				'slidesPerView'               => array('type' => 'text'  , 'default' => $data['slidesPerView']              , 'description' => $descriptions['slidesPerView']              , 'group' => __('Display', 'slideshow-jquery-image-gallery')),
				'maxWidth'                    => array('type' => 'text'  , 'default' => $data['maxWidth']                   , 'description' => $descriptions['maxWidth']                   , 'group' => __('Display', 'slideshow-jquery-image-gallery')),
				'aspectRatio'                 => array('type' => 'text'  , 'default' => $data['aspectRatio']                , 'description' => $descriptions['aspectRatio']                , 'group' => __('Display', 'slideshow-jquery-image-gallery')                                                           , 'dependsOn' => array('settings[preserveSlideshowDimensions]', 'true')),
				'height'                      => array('type' => 'text'  , 'default' => $data['height']                     , 'description' => $descriptions['height']                     , 'group' => __('Display', 'slideshow-jquery-image-gallery')                                                           , 'dependsOn' => array('settings[preserveSlideshowDimensions]', 'false')),
				'imageBehaviour'              => array('type' => 'select', 'default' => $data['imageBehaviour']             , 'description' => $descriptions['imageBehaviour']             , 'group' => __('Display', 'slideshow-jquery-image-gallery')      , 'options' => array('natural' => __('Natural and centered', 'slideshow-jquery-image-gallery'), 'crop' => __('Crop to fit', 'slideshow-jquery-image-gallery'), 'stretch' => __('Stretch to fit', 'slideshow-jquery-image-gallery'))),
				'preserveSlideshowDimensions' => array('type' => 'radio' , 'default' => $data['preserveSlideshowDimensions'], 'description' => $descriptions['preserveSlideshowDimensions'], 'group' => __('Display', 'slideshow-jquery-image-gallery')      , 'options' => array('true' => $yes, 'false' => $no) , 'dependsOn' => array('settings[enableResponsiveness]', 'true')),
				'enableResponsiveness'        => array('type' => 'radio' , 'default' => $data['enableResponsiveness']       , 'description' => $descriptions['enableResponsiveness']       , 'group' => __('Display', 'slideshow-jquery-image-gallery')      , 'options' => array('true' => $yes, 'false' => $no)),
				'showDescription'             => array('type' => 'radio' , 'default' => $data['showDescription']            , 'description' => $descriptions['showDescription']            , 'group' => __('Display', 'slideshow-jquery-image-gallery')      , 'options' => array('true' => $yes, 'false' => $no)),
				'hideDescription'             => array('type' => 'radio' , 'default' => $data['hideDescription']            , 'description' => $descriptions['hideDescription']            , 'group' => __('Display', 'slideshow-jquery-image-gallery')      , 'options' => array('true' => $yes, 'false' => $no) , 'dependsOn' => array('settings[showDescription]', 'true')),
				'play'                        => array('type' => 'radio' , 'default' => $data['play']                       , 'description' => $descriptions['play']                       , 'group' => __('Control', 'slideshow-jquery-image-gallery')      , 'options' => array('true' => $yes, 'false' => $no)),
				'loop'                        => array('type' => 'radio' , 'default' => $data['loop']                       , 'description' => $descriptions['loop']                       , 'group' => __('Control', 'slideshow-jquery-image-gallery')      , 'options' => array('true' => $yes, 'false' => $no)),
				'pauseOnHover'                => array('type' => 'radio' , 'default' => $data['loop']                       , 'description' => $descriptions['pauseOnHover']               , 'group' => __('Control', 'slideshow-jquery-image-gallery')      , 'options' => array('true' => $yes, 'false' => $no)),
				'controllable'                => array('type' => 'radio' , 'default' => $data['controllable']               , 'description' => $descriptions['controllable']               , 'group' => __('Control', 'slideshow-jquery-image-gallery')      , 'options' => array('true' => $yes, 'false' => $no)),
				'hideNavigationButtons'       => array('type' => 'radio' , 'default' => $data['hideNavigationButtons']      , 'description' => $descriptions['hideNavigationButtons']      , 'group' => __('Control', 'slideshow-jquery-image-gallery')      , 'options' => array('true' => $yes, 'false' => $no) , 'dependsOn' => array('settings[controllable]', 'true')),
				'showPagination'              => array('type' => 'radio' , 'default' => $data['showPagination']             , 'description' => $descriptions['showPagination']             , 'group' => __('Control', 'slideshow-jquery-image-gallery')      , 'options' => array('true' => $yes, 'false' => $no)),
				'hidePagination'              => array('type' => 'radio' , 'default' => $data['hidePagination']             , 'description' => $descriptions['hidePagination']             , 'group' => __('Control', 'slideshow-jquery-image-gallery')      , 'options' => array('true' => $yes, 'false' => $no) , 'dependsOn' => array('settings[showPagination]', 'true')),
				'controlPanel'                => array('type' => 'radio' , 'default' => $data['controlPanel']               , 'description' => $descriptions['controlPanel']               , 'group' => __('Control', 'slideshow-jquery-image-gallery')      , 'options' => array('true' => $yes, 'false' => $no)),
				'hideControlPanel'            => array('type' => 'radio' , 'default' => $data['hideControlPanel']           , 'description' => $descriptions['hideControlPanel']           , 'group' => __('Control', 'slideshow-jquery-image-gallery')      , 'options' => array('true' => $yes, 'false' => $no) , 'dependsOn' => array('settings[controlPanel]', 'true')),
				'waitUntilLoaded'             => array('type' => 'radio' , 'default' => $data['waitUntilLoaded']            , 'description' => $descriptions['waitUntilLoaded']            , 'group' => __('Miscellaneous', 'slideshow-jquery-image-gallery'), 'options' => array('true' => $yes, 'false' => $no)),
				'showLoadingIcon'             => array('type' => 'radio' , 'default' => $data['showLoadingIcon']            , 'description' => $descriptions['showLoadingIcon']            , 'group' => __('Miscellaneous', 'slideshow-jquery-image-gallery'), 'options' => array('true' => $yes, 'false' => $no) , 'dependsOn' => array('settings[waitUntilLoaded]', 'true')),
				'random'                      => array('type' => 'radio' , 'default' => $data['random']                     , 'description' => $descriptions['random']                     , 'group' => __('Miscellaneous', 'slideshow-jquery-image-gallery'), 'options' => array('true' => $yes, 'false' => $no)),
				'avoidFilter'                 => array('type' => 'radio' , 'default' => $data['avoidFilter']                , 'description' => $descriptions['avoidFilter']                , 'group' => __('Miscellaneous', 'slideshow-jquery-image-gallery'), 'options' => array('true' => $yes, 'false' => $no))
			);
			*/
		}

		// Return
		return $data;
	}

	/**
	 * Returns an array of style setting defaults.
	 *
	 * For a full description of the parameters, see getAllDefaults().
	 *
	 * @since 2.1.20
	 * @param boolean $fullDefinition (optional, defaults to false)
	 * @param boolean $fromDatabase (optional, defaults to true)
	 * @return mixed $data
	 */
	static function getDefaultStyleSettings($fullDefinition = false, $fromDatabase = true)
	{
		// Default style settings
		$data = array(
			'style' => 'style-light.css'
		);

		// Read defaults from database and merge with $data, when $fromDatabase is set to true
		if ($fromDatabase)
		{
			$data = array_merge(
				$data,
				$customData = get_option(SlideshowPluginGeneralSettings::$defaultStyleSettings, array())
			);
		}

		// Full definition
		if ($fullDefinition)
		{
			$data = array(
				'style' => array('type' => 'select', 'default' => $data['style'], 'description' => __('The style used for this slideshow', 'slideshow-jquery-image-gallery'), 'options' => SlideshowPluginGeneralSettings::getStylesheets()),
			);
		}

		// Return
		return $data;
	}

	/**
	 * Returns an HTML inputField of the input setting.
	 *
	 * This function expects the setting to be in the 'fullDefinition'
	 * format that the getDefaults() and getSettings() methods both
	 * return.
	 *
	 * @since 2.1.20
	 * @param string $settingsKey
	 * @param string $settingsName
	 * @param mixed $settings
	 * @param bool $hideDependentValues (optional, defaults to true)
	 * @return mixed $inputField
	 */
	static function getInputField($settingsKey, $settingsName, $settings, $hideDependentValues = true)
	{
		if (!is_array($settings) ||
			empty($settings) ||
			empty($settingsName))
		{
			return null;
		}

		$inputField   = '';
		$name         = $settingsKey . '[' . $settingsName . ']';
		$displayValue = (!isset($settings['value']) || (empty($settings['value']) && !is_numeric($settings['value'])) ? $settings['default'] : $settings['value']);
		$class        = ((isset($settings['dependsOn']) && $hideDependentValues)? 'depends-on-field-value ' . $settings['dependsOn'][0] . ' ' . $settings['dependsOn'][1] . ' ': '') . $settingsKey . '-' . $settingsName;

		switch($settings['type'])
		{
			case 'text':

				$inputField .= '<input
					type="text"
					name="' . $name . '"
					class="' . $class . '"
					value="' . $displayValue . '"
				/>';

				break;

			case 'textarea':

				$inputField .= '<textarea
					name="' . $name . '"
					class="' . $class . '"
					rows="20"
					cols="60"
				>' . $displayValue . '</textarea>';

				break;

			case 'select':

				$inputField .= '<select name="' . $name . '" class="' . $class . '">';

				foreach ($settings['options'] as $optionKey => $optionValue)
				{
					$inputField .= '<option value="' . $optionKey . '" ' . selected($displayValue, $optionKey, false) . '>
						' . $optionValue . '
					</option>';
				}

				$inputField .= '</select>';

				break;

			case 'radio':

				foreach ($settings['options'] as $radioKey => $radioValue)
				{
					$inputField .= '<label style="padding-right: 10px;"><input
						type="radio"
						name="' . $name . '"
						class="' . $class . '"
						value="' . $radioKey . '" ' .
						checked($displayValue, $radioKey, false) .
						' />' . $radioValue . '</label>';
				}

				break;

			default:

				$inputField = null;

				break;
		};

		// Return
		return $inputField;
	}
}
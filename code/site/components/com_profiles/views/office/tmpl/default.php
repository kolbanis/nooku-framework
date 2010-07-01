<? /** $Id$ */ ?>
<? defined('KOOWA') or die('Restricted access'); ?>
<?= @helper('behavior.mootools'); ?>

<script>var coordinate_lat = <?=@helper('admin::com.profiles.helper.geocoding.coordinates', 'latitude', $office) ?>;</script>
<script>var coordinate_lng = <?=@helper('admin::com.profiles.helper.geocoding.coordinates', 'longitude', $office) ?>;</script>

<style src="media://com_profiles/css/default.css" />
<script src="media://com_profiles/js/site.office.js" />
<script src="http://maps.google.com/maps/api/js?sensor=false" />

<h1 class="componentheading"><?= $office->title; ?></h1>
<div id="profiles_info">
	<img src="<?= @$mediaurl;?>/com_profiles/images/flags/<?= strtolower($office->country);?>.png" alt="<?= $office->country;?> flag"/>
	<?= $office->address?>
	<span class="phone"><?= $office->phone; ?></span>
	<span class="fax"><?= $office->fax; ?></span>
	<span class="people">
		<a href="<?=@route('view=people&profiles_office_id='.$office->id) ?>"><?= $office->people; ?> <?=@text('Employee(s)')?></a>
	</span>
	<!-- @todo
	<span class="map">
		<a href="#" onclick="showAddress(<?= $office->address1 . ' ' . $office->address2 . ', ' . $office->city . ', ' . $office->state . ', ' . $office->postcode . ', ' . $office->country; ?>); return false"><?= @text('Map'); ?></a>
	</span>
	 -->
</div>
<div id="profiles_desc">
	<h2><?= @text('Information'); ?></h2>
	<?= $office->description; ?>
</div>

<div id="map_canvas" style="width: 100%; height: 300px"></div>
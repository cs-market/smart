<?php

use Tygh\Registry;

$company_id = fn_get_default_company_id();
$page_id = db_get_field('SELECT page_id FROM ?:pages WHERE parent_id = ?i AND page_type = ?s', 0, PAGE_TYPE_BLOG);
$url = Registry::get('config.dir.addons') . 'azure_theme/demo_data/images/';

$schema = array(
	'Nikon' => array (
		'page_type' => PAGE_TYPE_BLOG,
		'page' => 'Nikon',
		'company_id' => $company_id,
		'description' => "<p>Nikon is the world leader in digital imaging, precision optics and 
			photo imaging technology and is globally recognized for setting new 
			standards in product design and performance. The unique strength of the 
			Nikon brand is attributable to the company’s unwavering commitment to 
			quality, performance, technology and innovation. Nikon Inc. markets and 
			distributes consumer and professional digital SLR cameras, NIKKOR 
			optics, Nikon 1 and COOLPIX digital cameras, Speedlights and system 
			accessories.
			</p>",
		'status' => 'A',
	    'image_path' => $url . 'nikon.jpg'
	),
	'Xbox' => array (
		'page_type' => PAGE_TYPE_BLOG,
		'page' => 'Xbox',
		'company_id' => $company_id,
		'description' => "<p><em>The original Xbox One had a pretty rocky start to life.</em>
			</p>
			<p>It launched with a compulsory accessory (the Kinect), that received a lukewarm reception, and its set-top box capabilities that weren't important for gamers.
			</p>
			<p>Microsoft has remedied many of these problems with the more recent Xbox One S , which features HDR compatibility and an Ultra HD Blu-ray player.
			</p>
			<p>With the new console now out, are there still reasons to consider its older brother considering how big and bulky it now looks in comparison? In short: yes.
			</p>
			<p>The first reason is price. With over a hundred dollars separating the two consoles, those on a budget stand to save a significant amount of money by opting to by the One over the One S.
			</p>
			<p>Yes, you'll be sacrificing some of the advanced features like 4K output, but if you don't have a 4K TV then there's little reason for you to bother.
			</p>
			<p>You have even more reason to stay with the original Xbox One if you're a fan of Microsoft's Kinect, which divided opinion when it was included with every Xbox One upon the console's initial release.
			</p>
			<p>The reason for this is that the One S doesn't include a Kinect port on its rear, meaning that you'll have to buy an adaptor if you want to use your camera accessory.
			</p>
			<p>It might not be perfect, but it's far better than the system Microsoft once pitched us on: an always-online console that would have allowed for disc-less play, easy game sharing on other owner's consoles, mandatory system scans and an end to second-hand purchases as we know them.
			</p>
			<p>With a refreshed interface and improved functionality, Microsoft's all-in-one system is taking on the PS4 head-on. Recent improvements like a new guide button that allows for easier and faster access to party chat and achievements, alongside a boost in responsiveness and more integrated community pages are empowering the system to fulfill its destiny as the epicenter of our home entertainment cabinet.
			</p>",
		'status' => 'A',
	    'image_path' => $url . 'xbox.jpg'
	),
	'Xiaomi' => array (
		'page_type' => PAGE_TYPE_BLOG,
		'page' => 'Xiaomi Mi Air (13.3-inch) Notebook Review',
		'company_id' => $company_id,
		'description' => "<p><strong>Successful debut.</strong> <em>Xiaomi
			 delivers its first product into the notebook field with the Mi Notebook
			 Air. The Chinese manufacturer does not do things in a small way and 
			wants to play in a league with established competitors. You can read in 
			our detailed test review whether the upstart can perform such a feat or 
			not.
				</em>
			</p>
			<p>The fact that a new player appears on the laptop market does have
			 scarcity value. After all, the PC market is shrinking overall, meaning 
			that low sales are concentrated over fewer and fewer manufacturers - 
			even now an acquisition of Fujitsu's PC operation by market leader 
			Lenovo is under discussion.
			</p>
			<p>Market entries by Huawei (with the MateBook) and Xiaomi are 
			greatly appreciated even though they are acting contrary to the trend. 
			Also, this new participant can get the market moving by increasing the 
			competition. However, one must say that Xiaomi and Huawei chose specific
			 branches of the PC market for their entries. Huawei chose the 2-in-1 
			branch and Xiaomi chose the sector for slim Ultrabooks. These market 
			fields can probably offer the best growth opportunities.
			</p>
			<p>Let's focus on the Xiaomi device: Xiaomi is a very successful 
			manufacturer in the smartphone market which is, however, regionally 
			concentrated in China. Xiaomi's business model consists of offering as 
			good devices as possible at low prices to undercut the competition, 
			therefore low or even no profit from hardware purchases is accepted. 
			This strategy showed great success in the smartphone market, at least 
			until 2015, when Xiaomi weakened a bit. Now that success could be 
			repeated in the PC market with the here reviewed Mi Notebook Air.
			</p>
			<p>As we proverbially look upon Xiaomi's first work, there is, of 
			course, no previous device to compare the Mi Notebook Air 13.3 with. 
			However, the laptop does have a smaller sibling in the form of the Mi 
			Notebook Air 12.5. This one is equipped with Y-class CPUs (TDP 4.5 W). 
			The here tested 13.3-inch model in comparison has the stronger 
			processors of the U-class (TDP 15 W). Neither device is officially 
			available in Germany, as with the smartphones Xiaomi focuses on the 
			Chinese home market. It is possible to import them though (including all
			 risks involved as the Mi Air does not have a CE-label, see also our 
			article with the topic Import from China), for example via the importer 
			Trading Shenzhen. From there we also ordered our test device which is 
			equivalent to the only configuration of the Mi Notebook Air 13.3 
			available to order with i5, 256 GB SSD, and 8 GB RAM. The whole thing 
			can be purchased for 867 Euros (~$942, without customs/ import costs or 
			shipping!).
			</p>
			<h2>Case</h2>
			<p>'Plain.' This word describes the design of the Mi 
			Notebook Air best. To create a neat design the manufacturer even goes as
			 far as to do without its logo on the display cover. Only a small, 
			silver Mi-logo is placed underneath the display. Also, the labeling of 
			interfaces is given up on. Apart from that, the design is quite 
			appropriate but also a little unoriginal. You do not have to be an 
			expert to recognize similarities to Apple's MacBook design; Xiaomi does 
			not take any big risks here.
			</p>
			<p>The entire case is, with exception 
			of the relatively thin display frame, silver-colored. Together with the 
			touchpad it is the only part of the case that is not made out of 
			aluminum because a glass pane is located in front of the display. In 
			terms of stability the case can definitely convince, it is built in a 
			unibody design. Neither the wrist rest, keyboard area nor the bottom can
			 be considerably pushed in. The relatively thin display cover can be 
			warped a little by a single twisting motion; nevertheless, the stability
			 in this area is overall also on a very high level. Even looking at the 
			haptics there is nothing to declare, the device feels like it is high 
			quality.
			</p>
			<p>Regarding the hinge, the design is obvious: It has a 
			single hinge that takes over nearly the whole case width, exactly like 
			the Apple MacBook. The hinge allows a maximum opening angle of approx. 
			130 ° which is a little restrictive in comparison to several other 
			notebooks (e.g. Dell Latitudes, Lenovo ThinkPads). Besides that, the 
			Xiaomi hinge is adjusted well and the display cover can be opened with 
			one hand. In bumpy environments, for example on a train, the display 
			shakes a little bit. Overall, the hinge does have the display cover 
			under control though.
			</p>",
		'status' => 'A',
	    'image_path' => $url . 'xiaomi-mi-notebook-air-125.jpg'
	),
	'Smart TV' => array (
		'page_type' => PAGE_TYPE_BLOG,
		'page' => 'What is smart TV?',
		'company_id' => $company_id,
		'description' => "<p><em>Want to access apps such as BBC iPlayer, 
			stream films on Netflix or surf websites on the big screen? You can do 
			all this with a smart TV.
				</em>
			</p>
			<h2>Smart TV: What are the benefits?</h2>
			<p>Just want to see great 
			smart TVs? We've got hundreds of expertly-tested models to suit all 
			needs and budgets in our TV reviews. Internet-connected smart features 
			help you get more out of your television. Most new TVs are now smart, 
			with a wide range of models to choose from, including Best Buy smart TVs
			 available at affordable prices. In this guide, we'll explain what you 
			get with smart TV and show you some of
			</p>
			<p>The vast majority of modern televisions now have 'smart' 
			capability, and it's getting increasingly hard to buy a non-smart model.
			 You don't need to connect a smart TV up to the internet to just watch 
			television, but if you do go online there are various benefits, 
			including:
			</p>
			<ul>
				<li><strong>Apps:</strong> Apps on smart TVs
			 either come pre-loaded, or are available to download from an app store.
			 Most smart TVs offer TV and film streaming on services like Netflix and
			 Amazon, catch-up TV on apps such as BBC iPlayer, and social networking 
			on Facebook and Twitter. 
				</li>
				<li><strong>Web browsing:</strong> Most 
			smart-TV models have built-in web browsers allowing you to surf the 
			internet and view web pages, photos and videos from the comfort of your 
			sofa. However, some are much easier to use than others. 
				</li>
				<li><strong>Additional services:</strong> 
			Smart-TV brands offer additional services to differentiate their smart 
			TVs from the competition, such as customisable homescreens and 
			recommendations of things to watch based on your personal tastes. Some 
			are useful, others feel like gimmicks.
				</li>
			</ul>",
		'status' => 'A',
	    'image_path' => $url . 'hero-image-tv.jpg'
	),
	'Smartphones' => array (
		'page_type' => PAGE_TYPE_BLOG,
		'page' => 'Smartphones',
		'company_id' => $company_id,
		'description' => "<h2><br></h2>
			<p><em>The best smartphone right now is the Samsung Galaxy S7 Edge</em>
			</p>
			<p>Thinking of buying a new phone? We've got the best smartphones of
			 the moment all listed here - and with MWC 2017 now out of the way, 
			we've got the LG G6 and Sony Xperia XZ Premium landing in the list soon.
			</p>
			<p>The Samsung Galaxy S8 is launching on March 29 in New York and London, and will be joined by the Samsung Galaxy S8 Plus too.
			</p>
			<p>But if you're after a top smartphone right now, here's the 
			ranking we've spent hours whittling down to a top ten, taking into 
			account the power, specs, design and, most importantly, value for money 
			of each handset.
			</p>
			<p>(If the price is too high, check out our list of the best cheap handsets that won't cost you more than £200).
			</p>
			<h2>Best smartphone</h2>
			<p>For those in a rush, here you go: the Samsung Galaxy S7 Edge is the best smartphone in the world.
			</p>
			<p>Its combination of great camera, stunning looks and sleek body, 
			alongside the world's best screen, make it an easy win... and lower 
			prices of late have made it an even better buy.
			</p>",
		'status' => 'A',
	    'image_path' => $url . 'smartphones-slider1.jpg'
	),
);



return $schema;

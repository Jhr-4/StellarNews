<?php
require(__DIR__ . "/../../partials/nav.php");
if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    redirect("home.php");
}

if(isset($_GET["articleDays"])){
$result = get('https://spacenews.p.rapidapi.com/datenews/1', "SPACE_API_KEY", $data = ["days" => $_GET["articleDays"]], true, 'spacenews.p.rapidapi.com');
// cached data for testing
/*$result = [
    'status' => 200,
    'response' => 
    '[
        {
            "id":638,
            "timestamp":"2024-04-03T18:23:10.297Z",
            "title":"Rock Samp led by NASA\'s Perseverance Embodies Why Rover Came to Mars",
            "site_url":"https://mars.nasa.gov/news/9572/",
            "image_url":"https://mars.nasa.gov/system/news_items/main_images/9572_PIA26314.gif",
            "news_text":"Perseverance Cores \'Bunsen Peak\' : The 21st rock core captured by NASA\'s Perseverance has a composition that would make it good at trapping and preserving signs of microbial life, if any was once present. The sample – shown being taken here – was cored from \\"Bunsen Peak\\" on March 11, the 1,088th Martian day, or sol, of the mission. Credits: NASA/JPL-Caltech. Download image › The 24th sample taken by the six-wheeled scientist offers new clues about Jezero Crater andthe lake it may have once held. Analysis by instruments aboard NASA’s Perseverance Mars rover indicate that the latest rock core taken by the rover was awash in water for an extended period of time in the distant past, perhaps as part of an ancient Martian beach. Collected on March 11, the sample is the rover’s 24th – a tally that includes 21 sample tubes filled with rock cores, two filled with regolith (broken rock and dust), and one with Martian atmosphere. \\"To put it simply, this is the kind of rock we had hoped to find when we decided to investigate Jezero Crater,\\" said Ken Farley, project scientist for Perseverance at Caltech in Pasadena, California. \\"Nearly all the minerals in the rock we just sampled were made in water; on Earth, water-deposited minerals are often good at trapping and preserving ancient organic material and biosignatures. The rock can even tell us about Mars climate conditions that were present when it was formed.\\" The presence of these specific minerals is considered promising for preserving a rich record of an ancient habitable environment on Mars. Such collections of minerals are important for guiding scientists to the most valuable samples for eventual return to Earth with the Mars Sample Return campaign. Meet the Mars Samples: Comet Geyser (Sample 24): Meet the 24th Martian sample collected by NASA\'s Mars Perseverance rover – \\"Comet Geyser,\\" a sample taken from a region of Jezero Crater that is especially rich in carbonate, a mineral linked to habitability. Credits: NASA/JPL-Caltech. Download video › Edge of the Crater’s Rim Nicknamed \\"Bunsen Peak\\" for the Yellowstone National Park landmark, the rock – about 5.6 feet wide and3.3 feet high (1.7 meters by 1 meter) – intrigued Perseverance scientists because the outcrop stands tall amid the surrounding terrain and has an interesting texture on one of its faces. They were also interested in Bunsen Peak’s vertical rockface, whichoffers a nice cross-section of the rock and, because it’s not flat-lying, is less dusty and therefore easier for science instruments to investigate. Before taking the sample, Perseverance scanned the rock using the rover’s SuperCam spectrometers and the X-ray spectrometer PIXL, short for Planetary Instrument for X-ray Lithochemistry. Then the rover used the rotor on the end of its robotic arm to grind (or abrade) a portion of the surface and scanned the rock again. The results: Bunsen Peak looks to be composed of about 75% carbonate grains cemented together by almost pure silica. Perseverance\'s View of \'Bunsen Peak\': This mosaic shows a rock called \\"Bunsen Peak\\" where NASA\'s Perseverance Mars rover extracted its 21st rock core and abraded a circular patch to investigate the rock\'s composition. Credits: NASA/JPL-Caltech/ASU/MSSS . Download image › \\"The silica and parts of the carbonate appear microcrystalline, which makes them extremely good at trapping and preserving signs of microbial life that might have once lived in this environment,\\" said Sandra Siljeström, a Perseverance scientist from the Research Institutes of Sweden (RISE) in Stockholm. \\"That makes this sample great for biosignature studies if returned to Earth. Additionally, the sample might be one of the older cores collected so far by Perseverance, and that is important because Mars was at its most habitable early in its history.\\" A potentialbiosignature is a substance or structure that could be evidence of past life but mayalso have been produced without the presence of life. Perseverance\'s \'Bunsen Peak\' Sample: Perseverance\'s CacheCam captured this image of the rover\'s latest cored sample – taken from an intriguing rock called \\"Bunsen Peak\\" – on March 11. Credits: NASA/JPL-Caltech. Download image › The Bunsen Peak sample is the third that Perseverance has collected while exploring the \\"Margin Unit,\\" a geologic area that hugs the inner edge of Jezero Crater’s rim. \\"We’re still exploring the margin and gathering data, but results so far may support our hypothesis that the rocks here formed along the shores of an ancient lake,\\" said Briony Horgan, a Perseverance scientist from Purdue University, in West Lafayette, Indiana. \\"The science team is also considering other ideas for the origin of the Margin Unit, as there are other ways to form carbonate and silica. But no matter how this rock formed, it is really exciting to geta sample.\\" The rover is working its way toward the westernmost portion of the Margin Unit. At the base of Jezero Crater’s rim, a location nicknamed \\"Bright Angel\\" is of interest to the science team because it may offer the first encounter with the much older rocks that make up the crater rim. Once it’s done exploring Bright Angel, Perseverance will begin an ascent of several months to the rim’s top. More About the Mission A key objective for Perseverance’s mission on Mars is astrobiology, includingcaching samples that may contain signs of ancient microbial life. The rover will characterize the planet’s geology and past climate, pave the way for human exploration of the Red Planet, and be the first mission to collect and cache Martian rock and regolith. Subsequent NASA missions, in cooperation with ESA (European Space Agency), would send spacecraft to Mars to collect these sealed samples from the surface and returnthem to Earth for in-depth analysis. The Mars 2020 Perseverance mission is part of NASA’s Moon to Mars exploration approach, which includes Artemis missions to the Moon that will help prepare for human exploration of the Red Planet. NASA’s Jet PropulsionLaboratory, which is managed for the agency by Caltech, built and manages operationsof the Perseverance rover. For more about Perseverance: https://mars.nasa.gov/mars2020/ News Media Contacts DC Agle Jet Propulsion Laboratory, Pasadena, Calif. 818-393-9011 agle@jpl.nasa.gov Karen Fox / Charles Blue NASA Headquarters, Washington 301-286-6284 / 202-802-5345 karen.c.fox@nasa.gov / charles.e.blue@nasa.gov",
            "news_summary_long":"Perseverance Cores \'Bunsen Peak\' has a composition that would make it good at trapping and preserving signs of microbial life, if any was once present. Analysis by instruments aboard NASA’s Perseverance Mars rover indicate that the latest rock core taken by the rover was awash in water for an extended period of time in the distant past. The rock can even tell us about Mars climate conditions that were present when it was formed.",
            "news_summary_short":"The 24th sample taken by the six-wheeled scientist offers new clues about Jezero Crater and the lake it may have once held.",
            "hashtags":"{\\"#Space\\",\\"#Marsrover\\",\\"#PerseveranceCores\\"}"
        },
        {
            "id":637,
            "timestamp":"2024-04-03T17:22:12.201Z",
            "title":"AI at the crossroads of cybersecurity, space and national security in the digital age",
            "site_url":"https://spacenews.com/ai-crossroads-cybersecurity-space-national-security-digital-age/",
            "image_url":"https://i0.wp.com/spacenews.com/wp-content/uploads/2024/04/Geostationary_orbit_pillars-credit-ESA.jpg",
            "news_text":"Technological prowess, especially regarding humanity’s increased presence in space, is increasingly becoming the linchpin of global competitiveness and national security. There, new opportunities to integrate AI are accompanied by a new generation of risks. Artificial intelligence in particular plays a crucial role in democratizing access to space exploration and research, opening it to many beyond just governmental space agencies, as evidenced by the large number of commercially financed and operated space launches over the last five years. As launch companies adopt AI-enabled autonomous flight safety systems, Space Launch Delta 45 is saving on mission control chairs and looping out about a dozen facilities across the base per launch. SpaceX uses an AI autopilot system to enable its Falcon 9 craft to carry out autonomous operations, such as docking with the International Space Station. Today’s AI capabilities enable a breadth of advantages that yesterday’s space 1.0 pioneers could only have imagined. AI can continuously monitor the trajectories of space debris and satellites in real-time, calculating the risk of potential collisions. By predicting close encounters well in advance, AI systems can automatically suggest or even execute maneuvers to avoid collisions, ensuring the safety and longevity of satellites. This will reduce the amount of personnel needed to conduct even the most highly complex space missions. AI can assist in simulating and testing satellite components and systems, reducing the need for expensive physical prototypes. Satellite communications are also improved by optimizing network bandwidth and resources, while AI algorithms quickly analyze massive amounts of satellite imagery and data transmitted over those same networks. New AI-derived scientific insights inform critical terrestrial capabilities such as weather forecasting, agriculture, urban planning, environmental monitoring and more. AI can even help predict and track the path of the growing volume of space debris to improve space situational awareness. By harnessing the power of AI, the barriers to entry for space exploration and utilization are significantly reduced, enabling a broader spectrum of participants to engage with space, from startups and universities to nations that previously lacked access to space. More players, more risks The democratization of space enhances mission efficiency and broadens participation in the space economy. However, it also introduces complex cybersecurity challenges for space-based assets crucial to national security. And that exposes the need for a new generation of technologies to be used in conjunction with existing investments. This is again where AI emerges as a crucial factor. For each benefit AI enables in spaceflight and satellite design and communications, there is a converse risk of attack, infiltration and compromise. The potential of AI to generate malware capable of evading current security measures presents a real challenge. Adversaries can train AI using data from past breaches to access advanced threat detection software, creating a cycle of escalating cyber-attacks and defensive measures. The unfortunate asymmetry of cyber-attacks is striking: bad actors need only a single breach to wreak significant damage, while defenders must be constantly vigilant to safeguard against threats that could present anywhere. The integration of AI technologies in crafting sophisticated fictitious news, disinformation, phishing emails, utilizing deep fake technology for fraud, and generating fake audio content with deceptive intent represents a significant evolution in cyber threats, especially with the recent rise of generative AI tech. This interconnected web of AI-powered behavior, seen in both space security concerns and the spread of misinformation through deep fake news, underscores the critical need for advanced cybersecurity measures and vigilance across all domains of technology and communication. For each benefit AI enables in spaceflight and satellite design and communications, there is a converse risk of attack, infiltration and compromise. Even while AI can be used to generate new threats and risks, it can conversely be applied to alleviating some of the burdens in complex security processes. By harnessing AI, security professionals can effectively manage the overwhelming five Vs of big data — volume, velocity, variety, veracity and value — enhancing data utility while ensuring its accuracy. This advantage is critical at a time marked by a shortage of professionals capable of manually handling these functions. This manifests itself especially in the areas of both space domain awareness and constellation management. As more active payloads and debris occupy the same orbits, the ability to react by changing orbit to prevent collisions grows increasingly important. Security beyond the Kármán line We are wielding a dual-edged sword: the opportunities these AI advancements offer and the new vulnerabilities they introduce. As we venture further into an era marked by cybersecurity challenges, infrastructure innovations, and the quest for workforce efficiencies, organizations that effectively leverage AI in their cybersecurity strategies, especially in the context of space and sensor technologies, will not only protect their operations but also gain a competitive edge. A future characterized by the intersection of AI, cyber, space, satellites and sensors holds the promise of resilience, innovation and security. However, this future also presents opportunities for adversaries to disrupt without significant infrastructure investments. That necessitates vigilance, adaptability and a commitment to thoughtful AI regulation. To maintain its strategic edge, the United States, alongside businesses navigating the modern space economy and national defense intricacies, must adeptly balance the benefits of unprecedented new efficiencies that will be realized as the result of the implementation of AI across the satellite design, launch and operations spectrum. There will be enormous cost savings there. However, there will be additional costs to security and defense of satellites as a result of the same AI implementation by bad actors in the community, who may launch attacks against satellites post-launch and during manufacturing. There is a real need for a secure data sharing process for both discovered and potential vulnerabilities in the supply chain. Finding a way to encourage cooperation (before there are mandates) between corporations and government will be critical in keeping pace with both the promise that AI will clearly provide and the threats that will come with it. Engaging with the latest AI developments, understanding their cybersecurity implications and anticipating technological breakthroughs are essential for securing a prosperous future in this dynamic environment. Paul Maguire is the CEO and Co-founder of Knowmadics, an integrated software developer with a focus on security requirements for both terrestrial and space-based assets. He is a former Naval Intelligence Officer specializing in space collections, and civilian program manager for the Air Force Space and Reconnaissance Office involved with the design of future national space systems. Maguire has also co-authored papers on Multi-Spectral Imagery and Imagery Exploitation.",
            "news_summary_long":"Artificial intelligence plays a crucial role in democratizing access to space exploration and research. But it also introduces complex cybersecurity challenges for space-based assets crucial to national security. For each benefit AI enables in spaceflight and satellite design and communications, there is a converse risk of attack, infiltration and compromise.",
            "news_summary_short":"Though the implementation of AI into the space industry will bring many benefits, it will also introduce cybersecurity risks.",
            "hashtags":"{\\"#Space\\",\\"#AI\\",\\"#artificialintelligence\\"}"
        }
    ]'
];*/

error_log("Response: " . var_export($result, true));
if (se($result, "status", 400, false) == 200 && isset($result["response"])) {
    $result = json_decode($result["response"], true);
} else {
    $result = [];
}

$data= [];
foreach ($result as $article){
    $article["api_id"] = $article["id"];
    unset($article["id"]);
    $article["api_timestamp"] = $article["timestamp"];
    unset($article["timestamp"]);
    $temp["api_timestamp"] = str_replace("T", " ", $article["api_timestamp"]);
    $temp["api_timestamp"] = str_replace("Z", "", $temp["api_timestamp"]);
    $article["api_timestamp"] = $temp["api_timestamp"]; 
    unset($article["news_summary_short"]);
    unset($article["hashtags"]);
    array_push($data, $article);
}
$result = $data;


$db = getDB();
$query = "INSERT INTO `ArticlesTable` ";

//setting up colomns
$colomns = [];
foreach ($result as $articles){
    foreach($articles as $k => $v){
        if(!in_array("`$k`", $colomns)){
            array_push($colomns, "`$k`");
        }
    }
}
$query .= "(" . join(",", $colomns) . ")";

//setting up the rows
$query .= " VALUES ";
$params = [];
$counter = 0;
foreach ($result as $articles){
    $tempParams = [];
    $counter++;
    $query .= "(";
    foreach($articles as $k => $v){
        $tempParams[":$k".$counter] = $v;
        $params[":$k".$counter] = $v;
    }
    $query .= join(",", array_keys($tempParams)) .",";
    $query = rtrim($query,",");//removes the last comma before the ending prenthesis for that row
    $query .= "),";
}
$query = rtrim($query,",");//removes the last comma after all the rows are added
$query .= " ON DUPLICATE KEY UPDATE `api_id` = `api_id`"; //replace old value with old value
try {
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    flash("Sucessfully inserted data", "success");
} catch (PDOException $error) {
    error_log("Something went wrong with the query" . var_export($error, true));
    flash("An Error Occured", "danger");
}
echo "<pre>";
var_export($query);
echo "</pre>";
echo "<pre>";
var_export($params);
echo "</pre";


}
?>


<div class="container-fluid">
    <h1>Space Articles</h1>
    <p>Remember, we typically won't be frequently calling live data from our API, this is merely a quick sample. We'll want to cache data in our DB to save on API quota.</p>
    <form>
        <div>
        <?php render_input(["type"=>"text", "id"=>"articleDays", "name"=>"articleDays", "label"=>"Article Days", "rules"=>["required"=>true]]);?>
        <?php render_button(["text"=>"Fetch Articles", "type"=>"submit"]);?>
        </div>
    </form>
    <div class="row">
        <?php if(isset($result)): ?>
            <pre>
            <?php //var_export($articles);
            ?>
            </pre>
            <table>
                <?php $displayColomns = []; ?>
                    <thead>
                        <?php foreach ($result as $article) :?>
                        <?php  foreach($article as $k => $v) : ?>
                            <?php
                            if(!in_array("$k", $displayColomns)):?>
                            <td>
                                <?php
                                array_push($displayColomns, "$k");
                                se($k);
                                ?>
                            </td>
                            <?php endif; ?>
                        <?php endforeach;?>
                        <?php endforeach;?>
                    </thead>
                <?php foreach ($result as $article) :?>
                    <tbody>
                        <tr>
                            <?php  foreach($article as $k => $v) : ?>
                            <td><?php se($v); ?></td> 
                            <?php endforeach; ?>           
                        </tr>
                    </tbody>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
</div>
<?php
require(__DIR__ . "/../../partials/flash.php");
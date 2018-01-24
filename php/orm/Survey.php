<?php

require_once('User.php');
require_once('Plant.php');
require_once('ArthropodSighting.php');

class Survey
{
//PRIVATE VARS
			
	private static $DOMAIN_NAME = "caterpillarscount.unc.edu";
	private static $extraPaths = "";
	
	private static $HOST = "localhost";
	private static $HOST_USERNAME = "username";
	private static $HOST_PASSWORD = "password";
	private static $DATABASE_NAME = "CaterpillarsCount";
	
	private $id;							//INT
	private $observer;
	private $plant;
	private $observationMethod;
	private $notes;
	private $wetLeaves;
	private $plantSpecies;
	private $numberOfLeaves;
	private $averageLeafLength;
	private $herbivoryScore;
	
	private $deleted;

//FACTORY
	public static function create($observer, $plant, $observationMethod, $notes, $wetLeaves, $plantSpecies, $numberOfLeaves, $averageLeafLength, $herbivoryScore) {
		$dbconn = mysqli_connect(self::$HOST, self::$HOST_USERNAME, self::$HOST_PASSWORD, self::$DATABASE_NAME);
		if(!$dbconn){
			return "Cannot connect to server.";
		}
		
		
		$observer = self::validObserver($dbconn, $observer, $plant);
		$plant = self::validPlant($dbconn, $plant);
		$observationMethod = self::validObservationMethod($dbconn, $observationMethod);
		$notes = self::validNotes($dbconn, $notes);
		$wetLeaves = filter_var($wetLeaves, FILTER_VALIDATE_BOOLEAN);
		$plantSpecies = self::validPlantSpecies($dbconn, $plantSpecies);
		$numberOfLeaves = self::validNumberOfLeaves($dbconn, $numberOfLeaves);
		$averageLeafLength = self::validAverageLeafLength($dbconn, $averageLeafLength);
		$herbivoryScore = self::validHerbivoryScore($dbconn, $herbivoryScore);
		
		
		$failures = "";
		
		if($plant === false){
			$failures .= "Invalid plant. ";
		}
		else if($observer === false){
			$failures .= "You have not been authenticated for this site. ";
		}
		if($observationMethod === false){
			$failures .= "Select an observation method. ";
		}
		if($notes === false){
			$failures .= "Invalid notes. ";
		}
		if($plantSpecies === false){
			$failures .= "Invalid plant species. ";
		}
		if($numberOfLeaves === false){
			$failures .= "Number of leaves must be between 1 and 500. ";
		}
		if($averageLeafLength === false){
			$failures .= "Average leaf length must be between 1cm and 60cm. ";
		}
		if($herbivoryScore === false){
			$failures .= "Select an herbivory score. ";
		}
		
		if($failures != ""){
			return $failures;
		}
		
		mysqli_query($dbconn, "INSERT INTO Survey (`UserFKOfObserver`, `PlantFK`, `ObservationMethod`, `Notes`, `WetLeaves`, `PlantSpecies`, `NumberOfLeaves`, `AverageLeafLength`, `HerbivoryScore`) VALUES ('" . $observer->getID() . "', '" . $plant->getID() . "', '$observationMethod', '$notes', '$wetLeaves', '$plantSpecies', '$numberOfLeaves', '$averageLeafLength', '$herbivoryScore')");
		$id = intval(mysqli_insert_id($dbconn));
		mysqli_close($dbconn);
		
		return new Survey($id, $observer, $plant, $observationMethod, $notes, $wetLeaves, $plantSpecies, $numberOfLeaves, $averageLeafLength, $herbivoryScore);
	}
	private function __construct($id, $observer, $plant, $observationMethod, $notes, $wetLeaves, $plantSpecies, $numberOfLeaves, $averageLeafLength, $herbivoryScore) {
		$this->id = intval($id);
		$this->observer = $observer;
		$this->plant = $plant;
		$this->observationMethod = $observationMethod;
		$this->notes = $notes;
		$this->wetLeaves = filter_var($wetLeaves, FILTER_VALIDATE_BOOLEAN);
		$this->plantSpecies = $plantSpecies;
		$this->numberOfLeaves = intval($numberOfLeaves);
		$this->averageLeafLength = intval($averageLeafLength);
		$this->herbivoryScore = $herbivoryScore;
		
		$this->deleted = false;
	}

//FINDERS
	public static function findByID($id) {
		$dbconn = mysqli_connect(self::$HOST, self::$HOST_USERNAME, self::$HOST_PASSWORD, self::$DATABASE_NAME);
		$id = mysqli_real_escape_string($dbconn, $id);
		$query = mysqli_query($dbconn, "SELECT * FROM `Survey` WHERE `ID`='$id' LIMIT 1");
		mysqli_close($dbconn);
		
		if(mysqli_num_rows($query) == 0){
			return null;
		}
		
		$surveyRow = mysqli_fetch_assoc($query);
		
		$observer = User::findByID($surveyRow["UserFKOfObserver"]);
		$plant = Plant::findByID($surveyRow["PlantFK"]);
		$observationMethod = $surveyRow["ObservationMethod"];
		$notes = $surveyRow["Notes"];
		$wetLeaves = $surveyRow["WetLeaves"];
		$plantSpecies = $surveyRow["PlantSpecies"];
		$numberOfLeaves = $surveyRow["NumberOfLeaves"];
		$averageLeafLength = $surveyRow["AverageLeafLength"];
		$herbivoryScore = $surveyRow["HerbivoryScore"];
		
		return new Survey($id, $observer, $plant, $observationMethod, $notes, $wetLeaves, $plantSpecies, $numberOfLeaves, $averageLeafLength, $herbivoryScore);
	}

//GETTERS
	public function getID() {
		if($this->deleted){return null;}
		return intval($this->id);
	}
	
	public function getObserver() {
		if($this->deleted){return null;}
		return $this->observer;
	}
	
	public function getPlant() {
		if($this->deleted){return null;}
		return $this->plant;
	}
	
	public function getObservationMethod() {
		if($this->deleted){return null;}
		return $this->observationMethod;
	}
	
	public function getNotes() {
		if($this->deleted){return null;}
		return $this->notes;
	}
	
	public function getWetLeaves() {
		if($this->deleted){return null;}
		return $this->wetLeaves;
	}
	
	public function getArthropodSightings() {
		if($this->deleted){return null;}
		return ArthropodSighting::findArthropodSightingsBySurvey($this);
	}
	
	public function getPlantSpecies() {
		if($this->deleted){return null;}
		return $this->plantSpecies;
	}
	
	public function getNumberOfLeaves() {
		if($this->deleted){return null;}
		return intval($this->numberOfLeaves);
	}
	
	public function getAverageLeafLength() {
		if($this->deleted){return null;}
		return intval($this->averageLeafLength);
	}
	
	public function getHerbivoryScore() {
		if($this->deleted){return null;}
		return $this->herbivoryScore;
	}
	
//SETTERS
	
	
//REMOVER
	public function permanentDelete()
	{
		if(!$this->deleted)
		{
			$dbconn = mysqli_connect(self::$HOST, self::$HOST_USERNAME, self::$HOST_PASSWORD, self::$DATABASE_NAME);
			$arthropodSightings = ArthropodSighting::findArthropodSightingsBySurvey($this);
			for($i = 0; $i < count($arthropodSightings); $i++){
				$arthropodSightings[$i]->permanentDelete();
			}
			mysqli_query($dbconn, "DELETE FROM `Survey` WHERE `ID`='" . $this->id . "'");
			$this->deleted = true;
			mysqli_close($dbconn);
			return true;
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

//validity ensurance
	public static function validObserver($dbconn, $observer, $plant){
		if(get_class($plant) != "Plant" || !$plant->getSite()->validateUser($observer, "")){
			return false;
		}
		return $observer;
	}
	
	public static function validPlant($dbconn, $plant){
		if(get_class($plant) != "Plant"){
			return false;
		}
		return $plant;
	}
	
	public static function validObservationMethod($dbconn, $observationMethod){
		if($observationMethod != "Visual" && $observationMethod != "Beat sheet"){
			return false;
		}
		return $observationMethod;
	}
	
	public static function validNotes($dbconn, $notes){
		$notes = mysqli_real_escape_string($dbconn, $notes);
		return $notes;
	}
	
	public static function validPlantSpecies($dbconn, $plantSpecies){
		$plantSpecies = trim($plantSpecies);
		$plantSpeciesList = array(array("Swamp cottonwood", "Populus heterophylla"), array("Plains cottonwood", "Populus deltoides"), array("Quaking aspen", "Populus tremuloides"), array("Black cottonwood", "Populus balsamifera"), array("Fremont cottonwood", "Populus fremontii"), array("Narrowleaf cottonwood", "Populus angustifolia"), array("Silver poplar", "Populus alba"), array("Lombardy poplar", "Populus nigra"), array("Mesquite spp.", "Prosopis spp."), array("Honey mesquite ", "Prosopis glandulosa"), array("Velvet mesquite", "Prosopis velutina"), array("Screwbean mesquite", "Prosopis pubescens"), array("Cherry and plum spp.", "Prunus spp."), array("Pin cherry", "Prunus pensylvanica"), array("Black cherry", "Prunus serotina"), array("Chokecherry", "Prunus virginiana"), array("Peach", "Prunus persica"), array("Canada plum", "Prunus nigra"), array("American plum", "Prunus americana"), array("Bitter cherry", "Prunus emarginata"), array("Allegheny plum", "Prunus alleghaniensis"), array("Chickasaw plum", "Prunus angustifolia"), array("Sweet cherry", "Prunus avium"), array("Sour cherry", "Prunus cerasus"), array("European plum", "Prunus domestica"), array("Mahaleb cherry", "Prunus mahaleb"), array("Oak spp.", "Quercus spp."), array("California live oak", "Quercus agrifolia"), array("White oak", "Quercus alba"), array("Arizona white oak", "Quercus arizonica"), array("Swamp white oak", "Quercus bicolor"), array("Canyon live oak", "Quercus chrysolepis"), array("Scarlet oak", "Quercus coccinea"), array("Blue oak", "Quercus douglasii"), array("Durand oak", "Quercus sinuata"), array("Northern pin oak", "Quercus ellipsoidalis"), array("Emory oak", "Quercus emoryi"), array("Engelmann oak", "Quercus engelmannii"), array("Southern red oak", "Quercus falcata"), array("Cherrybark oak", "Quercus pagoda"), array("Gambel oak", "Quercus gambelii"), array("Oregon white oak", "Quercus garryana"), array("Scrub oak", "Quercus ilicifolia"), array("Shingle oak", "Quercus imbricaria"), array("California black oak", "Quercus kelloggii"), array("Turkey oak", "Quercus laevis"), array("Laurel oak", "Quercus laurifolia"), array("California white oak", "Quercus lobata"), array("Overcup oak", "Quercus lyrata"), array("Bur oak", "Quercus macrocarpa"), array("Blackjack oak", "Quercus marilandica"), array("Swamp chestnut oak", "Quercus michauxii"), array("Chinkapin oak", "Quercus muehlenbergii"), array("Water oak", "Quercus nigra"), array("Texas red oak", "Quercus texana"), array("Mexican blue oak", "Quercus oblongifolia"), array("Pin oak", "Quercus palustris"), array("Willow oak", "Quercus phellos"), array("Bigtooth aspen", "Populus grandidentata"), array("Chestnut oak", "Quercus prinus"), array("Northern red oak", "Quercus rubra"), array("Shumard oak", "Quercus shumardii"), array("Post oak", "Quercus stellata"), array("Delta post oak", "Quercus similis"), array("Black oak", "Quercus velutina"), array("Live oak", "Quercus virginiana"), array("Interior live oak", "Quercus wislizeni"), array("Dwarf post oak", "Quercus margarettiae"), array("Dwarf live oak", "Quercus minima"), array("Bluejack oak", "Quercus incana"), array("Silverleaf oak", "Quercus hypoleucoides"), array("Oglethorpe oak", "Quercus oglethorpensis"), array("Dwarf chinkapin oak", "Quercus prinoides"), array("Gray oak", "Quercus grisea"), array("Netleaf oak", "Quercus rugosa"), array("Chisos oak", "Quercus graciliformis"), array("Sea torchwood", "Amyris elemifera"), array("Pond-apple ", "Annona glabra"), array("Gumbo limbo ", "Bursera simaruba"), array("Sheoak spp.", "Casuarina spp."), array("Gray sheoak", "Casuarina glauca"), array("Belah", "Casuarina lepidophloia"), array("Camphortree", "Cinnamomum camphora"), array("Florida fiddlewood", "Citharexylum fruticosum"), array("Citrus spp.", "Citrus spp."), array("Tietongue", "Coccoloba diversifolia"), array("Soldierwood", "Colubrina elliptica"), array("Largeleaf geigertree", "Cordia sebestena"), array("Carrotwood", "Cupaniopsis anacardioides"), array("Bluewood", "Condalia hookeri"), array("Blackbead ebony", "Ebenopsis ebano"), array("Great leucaene", "Leucaena pulverulenta"), array("Texas sophora", "Sophora affinis"), array("Red stopper", "Eugenia rhombea"), array("Butterbough", "Exothea paniculata"), array("Florida strangler fig", "Ficus aurea"), array("Wild banyantree", "Ficus citrifolia"), array("Beeftree", "Guapira discolor"), array("Manchineel", "Hippomane mancinella"), array("False tamarind", "Lysiloma latisiliquum"), array("Mango", "Mangifera indica"), array("Florida poisontree", "Metopium toxiferum"), array("Fishpoison tree", "Piscidia piscipula"), array("Octopus tree", "Schefflera actinophylla"), array("False mastic", "Sideroxylon foetidissimum"), array("White bully", "Sideroxylon salicifolium"), array("Paradisetree", "Simarouba glauca"), array("Java plum", "Syzygium cumini"), array("Tamarind", "Tamarindus indica"), array("Black locust", "Robinia pseudoacacia"), array("New mexico locust", "Robinia neomexicana"), array("Everglades palm", "Acoelorraphe wrightii"), array("Florida silver palm", "Coccothrinax argentata"), array("Coconut palm ", "Cocos nucifera"), array("Royal palm spp.", "Roystonea spp."), array("Mexican palmetto", "Sabal mexicana"), array("Cabbage palmetto", "Sabal palmetto"), array("Key thatch palm", "Thrinax morrisii"), array("Florida thatch palm", "Thrinax radiata"), array("Other palms", "Family arecaceae not listed above"), array("Western soapberry", "Sapindus saponaria"), array("Willow spp.", "Salix spp."), array("Peachleaf willow", "Salix amygdaloides"), array("Black willow", "Salix nigra"), array("Bebb willow", "Salix bebbiana"), array("Bonpland willow", "Salix bonplandiana"), array("Coastal plain willow", "Salix caroliniana"), array("Balsam willow", "Salix pyrifolia"), array("White willow", "Salix alba"), array("Scouler's willow", "Salix scouleriana"), array("Weeping willow", "Salix sepulcralis"), array("Sassafras", "Sassafras albidum"), array("Mountain-ash spp.", "Sorbus spp."), array("American mountain-ash", "Sorbus americana"), array("European mountain-ash", "Sorbus aucuparia"), array("Northern mountain-ash", "Sorbus decora"), array("West indian mahogany", "Swietenia mahagoni"), array("Basswood spp.", "Tilia spp."), array("American basswood", "Tilia americana"), array("White basswood", "Tilia americana"), array("Carolina basswood", "Tilia americana"), array("Elm spp.", "Ulmus spp."), array("Winged elm", "Ulmus alata"), array("American elm", "Ulmus americana"), array("Cedar elm", "Ulmus crassifolia"), array("Siberian elm", "Ulmus pumila"), array("Slippery elm", "Ulmus rubra"), array("September elm", "Ulmus serotina"), array("Rock elm", "Ulmus thomasii"), array("California-laurel", "Umbellularia californica"), array("Joshua tree", "Yucca brevifolia"), array("Black-mangrove", "Avicennia germinans"), array("Buttonwood-mangrove", "Conocarpus erectus"), array("White-mangrove", "Laguncularia racemosa"), array("American mangrove", "Rhizophora mangle"), array("Desert ironwood", "Olneya tesota"), array("Saltcedar", "Tamarix spp."), array("Melaleuca", "Melaleuca quinquenervia"), array("Chinaberry", "Melia azedarach"), array("Chinese tallowtree", "Triadica sebifera"), array("Tungoil tree", "Vernicia fordii"), array("Smoketree", "Cotinus obovatus"), array("Russian-olive", "Elaeagnus angustifolia"), array("Washington hawthorn", "Crataegus phaenopyrum"), array("Fleshy hawthorn", "Crataegus succulenta"), array("Dwarf hawthorn", "Crataegus uniflora"), array("Berlandier ash", "Fraxinus berlandieriana"), array("Avocado", "Persea americana"), array("Graves oak", "Quercus gravesii"), array("Mexican white oak", "Quercus polymorpha"), array("Buckley oak", "Quercus buckleyi"), array("Lacey oak", "Quercus laceyi"), array("Anacahuita", "Cordia boissieri"), array("Fir spp.", "Abies spp."), array("Pacific silver fir", "Abies amabilis"), array("Balsam fir", "Abies balsamea"), array("Santa lucia or bristlecone fir", "Abies bracteata"), array("White fir", "Abies concolor"), array("Fraser fir", "Abies fraseri"), array("Grand fir", "Abies grandis"), array("Corkbark fir", "Abies lasiocarpa"), array("Subalpine fir", "Abies lasiocarpa"), array("California red fir", "Abies magnifica"), array("Shasta red fir", "Abies shastensis"), array("Noble fir", "Abies procera"), array("White-cedar spp.", "Chamaecyparis spp."), array("Port-orford-cedar", "Chamaecyparis lawsoniana"), array("Alaska yellow-cedar", "Chamaecyparis nootkatensis"), array("Atlantic white-cedar", "Chamaecyparis thyoides"), array("Cypress", "Cupressus spp."), array("Arizona cypress", "Cupressus arizonica"), array("Modoc cypress", "Cupressus bakeri"), array("Tecate cypress", "Cupressus forbesii"), array("Monterey cypress", "Cupressus macrocarpa"), array("Sargent's cypress", "Cupressus sargentii"), array("Macnab's cypress", "Cupressus macnabiana"), array("Redcedar/juniper spp.", "Juniperus spp."), array("Pinchot juniper", "Juniperus pinchotii"), array("Redberry juniper", "Juniperus coahuilensis"), array("Drooping juniper", "Juniperus flaccida"), array("Ashe juniper", "Juniperus ashei"), array("California juniper", "Juniperus californica"), array("Alligator juniper", "Juniperus deppeana"), array("Western juniper", "Juniperus occidentalis"), array("Utah juniper", "Juniperus osteosperma"), array("Rocky mountain juniper", "Juniperus scopulorum"), array("Southern redcedar", "Juniperus virginiana"), array("Eastern redcedar", "Juniperus virginiana"), array("Oneseed juniper", "Juniperus monosperma"), array("Larch spp.", "Larix spp."), array("Tamarack (native)", "Larix laricina"), array("Subalpine larch", "Larix lyallii"), array("Western larch", "Larix occidentalis"), array("Incense-cedar", "Calocedrus decurrens"), array("Spruce spp.", "Picea spp."), array("Norway spruce", "Picea abies"), array("Brewer spruce", "Picea breweriana"), array("Engelmann spruce", "Picea engelmannii"), array("White spruce", "Picea glauca"), array("Black spruce", "Picea mariana"), array("Blue spruce", "Picea pungens"), array("Red spruce", "Picea rubens"), array("Sitka spruce", "Picea sitchensis"), array("Pine spp.", "Pinus spp."), array("Whitebark pine", "Pinus albicaulis"), array("Bristlecone pine", "Pinus aristata"), array("Knobcone pine", "Pinus attenuata"), array("Foxtail pine", "Pinus balfouriana"), array("Jack pine", "Pinus banksiana"), array("Common pinyon", "Pinus edulis"), array("Sand pine", "Pinus clausa"), array("Lodgepole pine", "Pinus contorta"), array("Coulter pine", "Pinus coulteri"), array("Shortleaf pine", "Pinus echinata"), array("Slash pine", "Pinus elliottii"), array("Apache pine", "Pinus engelmannii"), array("Limber pine", "Pinus flexilis"), array("Southwestern white pine ", "Pinus strobiformis"), array("Spruce pine", "Pinus glabra"), array("Jeffrey pine", "Pinus jeffreyi"), array("Sugar pine", "Pinus lambertiana"), array("Chihuahua pine", "Pinus leiophylla"), array("Western white pine", "Pinus monticola"), array("Bishop pine", "Pinus muricata"), array("Longleaf pine", "Pinus palustris"), array("Ponderosa pine", "Pinus ponderosa"), array("Table mountain pine", "Pinus pungens"), array("Monterey pine", "Pinus radiata"), array("Red pine", "Pinus resinosa"), array("Pitch pine", "Pinus rigida"), array("Gray or california foothill pine", "Pinus sabiniana"), array("Pond pine", "Pinus serotina"), array("Eastern white pine", "Pinus strobus"), array("Scotch pine", "Pinus sylvestris"), array("Loblolly pine", "Pinus taeda"), array("Virginia pine", "Pinus virginiana"), array("Singleleaf pinyon", "Pinus monophylla"), array("Border pinyon", "Pinus discolor"), array("Arizona pine", "Pinus arizonica"), array("Austrian pine", "Pinus nigra"), array("Washoe pine", "Pinus washoensis"), array("Four-leaf or parry pinyon pine", "Pinus quadrifolia"), array("Torrey pine", "Pinus torreyana"), array("Mexican pinyon pine", "Pinus cembroides"), array("Papershell pinyon pine", "Pinus remota"), array("Great basin bristlecone pine", "Pinus longaeva"), array("Arizona pinyon pine", "Pinus monophylla"), array("Honduras pine", "Pinus elliottii"), array("Douglas-fir spp.", "Pseudotsuga spp."), array("Bigcone douglas-fir", "Pseudotsuga macrocarpa"), array("Douglas-fir", "Pseudotsuga menziesii"), array("Redwood", "Sequoia sempervirens"), array("Giant sequoia", "Sequoiadendron giganteum"), array("Baldcypress spp.", "Taxodium spp."), array("Baldcypress", "Taxodium distichum"), array("Pondcypress", "Taxodium ascendens"), array("Montezuma baldcypress", "Taxodium mucronatum"), array("Yew spp.", "Taxus spp."), array("Pacific yew", "Taxus brevifolia"), array("Florida yew", "Taxus floridana"), array("Thuja spp.", "Thuja spp."), array("Northern white-cedar", "Thuja occidentalis"), array("Western redcedar", "Thuja plicata"), array("Torreya spp.", "Torreya spp."), array("California torreya (nutmeg)", "Torreya californica"), array("Florida torreya (nutmeg)", "Torreya taxifolia"), array("Hemlock spp.", "Tsuga spp."), array("Eastern hemlock", "Tsuga canadensis"), array("Carolina hemlock", "Tsuga caroliniana"), array("Western hemlock", "Tsuga heterophylla"), array("Mountain hemlock", "Tsuga mertensiana"), array("Acacia spp.", "Acacia spp."), array("Sweet acacia", "Acacia farnesiana"), array("Catclaw acacia", "Acacia greggii"), array("Maple spp.", "Acer spp."), array("Florida maple", "Acer barbatum"), array("Bigleaf maple", "Acer macrophyllum"), array("Boxelder", "Acer negundo"), array("Black maple", "Acer nigrum"), array("Striped maple", "Acer pensylvanicum"), array("Red maple", "Acer rubrum"), array("Silver maple", "Acer saccharinum"), array("Sugar maple", "Acer saccharum"), array("Mountain maple", "Acer spicatum"), array("Norway maple", "Acer platanoides"), array("Rocky mountain maple", "Acer glabrum"), array("Bigtooth maple", "Acer grandidentatum"), array("Chalk maple", "Acer leucoderme"), array("Buckeye spp.", "Aesculus spp."), array("Ohio buckeye", "Aesculus glabra"), array("Yellow buckeye", "Aesculus flava"), array("California buckeye", "Aesculus californica"), array("Texas buckeye", "Aesculus glabra"), array("Red buckeye", "Aesculus pavia"), array("Painted buckeye", "Aesculus sylvatica"), array("Ailanthus", "Ailanthus altissima"), array("Mimosa", "Albizia julibrissin"), array("Alder spp.", "Alnus spp."), array("Red alder", "Alnus rubra"), array("White alder", "Alnus rhombifolia"), array("Arizona alder", "Alnus oblongifolia"), array("European alder", "Alnus glutinosa"), array("Serviceberry spp.", "Amelanchier spp."), array("Common serviceberry", "Amelanchier arborea"), array("Roundleaf serviceberry", "Amelanchier sanguinea"), array("Madrone spp.", "Arbutus spp."), array("Pacific madrone", "Arbutus menziesii"), array("Arizona madrone", "Arbutus arizonica"), array("Texas madrone", "Arbutus xalapensis"), array("Pawpaw", "Asimina triloba"), array("Birch spp.", "Betula spp."), array("Yellow birch", "Betula alleghaniensis"), array("Sweet birch", "Betula lenta"), array("River birch", "Betula nigra"), array("Water birch", "Betula occidentalis"), array("Paper birch", "Betula papyrifera"), array("Virginia roundleaf birch", "Betula uber"), array("Northwestern paper birch", "Betula x utahensis"), array("Gray birch", "Betula populifolia"), array("Chittamwood", "Sideroxylon lanuginosum"), array("American hornbeam", "Carpinus caroliniana"), array("Hickory spp.", "Carya spp."), array("Water hickory", "Carya aquatica"), array("Bitternut hickory", "Carya cordiformis"), array("Pignut hickory", "Carya glabra"), array("Pecan", "Carya illinoinensis"), array("Shellbark hickory", "Carya laciniosa"), array("Nutmeg hickory", "Carya myristiciformis"), array("Shagbark hickory", "Carya ovata"), array("Black hickory", "Carya texana"), array("Mockernut hickory", "Carya alba"), array("Sand hickory", "Carya pallida"), array("Scrub hickory", "Carya floridana"), array("Red hickory", "Carya ovalis"), array("Southern shagbark hickory", "Carya carolinae-septentrionalis"), array("Chestnut spp.", "Castanea spp."), array("American chestnut", "Castanea dentata"), array("Allegheny chinkapin", "Castanea pumila"), array("Ozark chinkapin", "Castanea pumila"), array("Chinese chestnut", "Castanea mollissima"), array("Giant chinkapin", "Chrysolepis chrysophylla"), array("Catalpa spp.", "Catalpa spp."), array("Southern catalpa", "Catalpa bignonioides"), array("Northern catalpa", "Catalpa speciosa"), array("Hackberry spp.", "Celtis spp."), array("Sugarberry", "Celtis laevigata"), array("Hackberry", "Celtis occidentalis"), array("Netleaf hackberry", "Celtis laevigata"), array("Eastern redbud", "Cercis canadensis"), array("Curlleaf mountain-mahogany", "Cercocarpus ledifolius"), array("Yellowwood", "Cladrastis kentukea"), array("Dogwood spp.", "Cornus spp."), array("Flowering dogwood", "Cornus florida"), array("Pacific dogwood", "Cornus nuttallii"), array("Hawthorn spp.", "Crataegus spp."), array("Cockspur hawthorn", "Crataegus crus-galli"), array("Downy hawthorn", "Crataegus mollis"), array("Brainerd's hawthorn", "Crataegus brainerdii"), array("Pear hawthorn", "Crataegus calpodendron"), array("Fireberry hawthorn", "Crataegus chrysocarpa"), array("Broadleaf hawthorn", "Crataegus dilatata"), array("Fanleaf hawthorn", "Crataegus flabellata"), array("Oneseed hawthorn", "Crataegus monogyna"), array("Scarlet hawthorn", "Crataegus pedicellata"), array("Eucalyptus spp.", "Eucalyptus spp."), array("Tasmanian bluegum", "Eucalyptus globulus"), array("River redgum", "Eucalyptus camaldulensis"), array("Grand eucalyptus", "Eucalyptus grandis"), array("Swampmahogany", "Eucalyptus robusta"), array("Persimmon spp.", "Diospyros spp."), array("Common persimmon", "Diospyros virginiana"), array("Texas persimmon", "Diospyros texana"), array("Anacua knockaway", "Ehretia anacua"), array("American beech", "Fagus grandifolia"), array("Ash spp.", "Fraxinus spp."), array("White ash", "Fraxinus americana"), array("Oregon ash", "Fraxinus latifolia"), array("Black ash", "Fraxinus nigra"), array("Green ash", "Fraxinus pennsylvanica"), array("Pumpkin ash", "Fraxinus profunda"), array("Blue ash", "Fraxinus quadrangulata"), array("Velvet ash", "Fraxinus velutina"), array("Carolina ash", "Fraxinus caroliniana"), array("Texas ash", "Fraxinus texensis"), array("Honeylocust spp.", "Gleditsia spp."), array("Waterlocust", "Gleditsia aquatica"), array("Honeylocust", "Gleditsia triacanthos"), array("Loblolly-bay", "Gordonia lasianthus"), array("Ginkgo", "Ginkgo biloba"), array("Kentucky coffeetree", "Gymnocladus dioicus"), array("Silverbell spp.", "Halesia spp."), array("Carolina silverbell", "Halesia carolina"), array("Two-wing silverbell", "Halesia diptera"), array("Little silverbell", "Halesia parviflora"), array("American holly", "Ilex opaca"), array("Walnut spp.", "Juglans spp."), array("Butternut", "Juglans cinerea"), array("Black walnut", "Juglans nigra"), array("Northern california black walnut", "Juglans hindsii"), array("Southern california black walnut", "Juglans californica"), array("Texas walnut", "Juglans microcarpa"), array("Arizona walnut", "Juglans major"), array("Sweetgum", "Liquidambar styraciflua"), array("Yellow-poplar", "Liriodendron tulipifera"), array("Tanoak", "Lithocarpus densiflorus"), array("Osage-orange", "Maclura pomifera"), array("Magnolia spp.", "Magnolia spp."), array("Cucumbertree", "Magnolia acuminata"), array("Southern magnolia", "Magnolia grandiflora"), array("Sweetbay", "Magnolia virginiana"), array("Bigleaf magnolia", "Magnolia macrophylla"), array("Mountain or fraser magnolia", "Magnolia fraseri"), array("Pyramid magnolia", "Magnolia pyramidata"), array("Umbrella magnolia", "Magnolia tripetala"), array("Apple spp.", "Malus spp."), array("Oregon crab apple", "Malus fusca"), array("Southern crab apple", "Malus angustifolia"), array("Sweet crab apple", "Malus coronaria"), array("Prairie crab apple", "Malus ioensis"), array("Mulberry spp.", "Morus spp."), array("White mulberry", "Morus alba"), array("Red mulberry", "Morus rubra"), array("Texas mulberry", "Morus microphylla"), array("Black mulberry", "Morus nigra"), array("Tupelo spp.", "Nyssa spp."), array("Water tupelo", "Nyssa aquatica"), array("Ogeechee tupelo", "Nyssa ogeche"), array("Blackgum", "Nyssa sylvatica"), array("Swamp tupelo", "Nyssa biflora"), array("Eastern hophornbeam", "Ostrya virginiana"), array("Sourwood", "Oxydendrum arboreum"), array("Paulownia empress-tree", "Paulownia tomentosa"), array("Bay spp.", "Persea spp."), array("Redbay", "Persea borbonia"), array("Water-elm planertree", "Planera aquatica"), array("Sycamore spp.", "Platanus spp."), array("California sycamore", "Platanus racemosa"), array("American sycamore", "Platanus occidentalis"), array("Arizona sycamore", "Platanus wrightii"), array("Cottonwood and poplar spp.", "Populus spp."), array("Balsam poplar", "Populus balsamifera"), array("Eastern cottonwood", "Populus deltoides"));
		for($i = 0; $i < count($plantSpeciesList); $i++){
			$plantSpeciesList[$i][0] = trim($plantSpeciesList[$i][0]);
			$plantSpeciesList[$i][1] = trim($plantSpeciesList[$i][1]);
			if($plantSpecies == $plantSpeciesList[$i][1]){
				return $plantSpeciesList[$i][0];
			}
			else if($plantSpecies == $plantSpeciesList[$i][0]){
				return $plantSpecies;
			}
		}
		return false;
	}
	
	public static function validNumberOfLeaves($dbconn, $numberOfLeaves){
		$numberOfLeaves = intval($numberOfLeaves);
		if($numberOfLeaves >= 1 && $numberOfLeaves <= 500){
			return $numberOfLeaves;
		}
		return false;
	}
	
	public static function validAverageLeafLength($dbconn, $averageLeafLength){
		$averageLeafLength = intval($averageLeafLength);
		if($averageLeafLength >= 1 && $averageLeafLength <= 60){
			return $averageLeafLength;
		}
		return false;
	}
	
	public static function validHerbivoryScore($dbconn, $herbivoryScore){
		if($herbivoryScore == "none" || $herbivoryScore == "trace" || $herbivoryScore == "light" || $herbivoryScore == "moderate" || $herbivoryScore == "heavy"){
			return $herbivoryScore;
		}
		return false;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

//FUNCTIONS
	public function addArthropodSighting($group, $length, $quantity, $notes, $hairy, $rolled, $tented){
		return ArthropodSighting::create($this, $group, $length, $quantity, $notes, $hairy, $rolled, $tented);
	}
}		
?>
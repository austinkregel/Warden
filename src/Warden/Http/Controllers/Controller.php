<?php

namespace Kregel\Warden\Http\Controllers;

use BadMethodCallException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Input;
use Request;
use Route;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Handle calls to missing methods on the controller.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @throws \BadMethodCallException
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        throw new BadMethodCallException("Method [$method] does not exist.");
    }

    /**
     * @param array $params
     *
     * @throws \Exception
     */
    public function checkParams(array $params)
    {
        foreach ($params as $p) {
            $config = config('kregel.warden.models');
            $configKeys = array_keys($config);
//            dd($p, $config, $configKeys, !in_array($p, $config), !in_array($p, $configKeys), !in_array($p, $configKeys) && !in_array($p, $config));
            if (in_array($p, $configKeys) && in_array($p, $config)) {
                throw new \Exception('You\'re attempting to use a value that isn\'t in the config! '.print_r($configKeys, true));
            }
        }
    }

    /**
     * @param $route
     * @param array $params
     *
     * @return mixed
     */
    public function get($route, $params = [])
    {
        $route = $this->parseRoute($route, $params);
        $request = Request::create($route, 'GET');
        $response = Route::dispatch($request);

        return $response;
    }

    /**
     * @param $route
     * @param array $params
     */
    public function post($route, $params = [])
    {
        $route = $this->parseRoute($route, $params);
        $request = Request::create($route, 'PUT', Input::all());
        $response = Route::dispatch($request);

        return $response;
    }

    /**
     * @param $route
     *
     * @return mixed
     */
    public function delete($route, $params = [])
    {
        $route = $this->parseRoute($route, $params);
        $request = Request::create($route, 'DELETE', Input::all());
        $response = Route::dispatch($request);

        return $response;
    }

    /**
     * @param $route
     *
     * @return mixed
     */
    public function put($route, $params = [])
    {
        $route = $this->parseRoute($route, $params);
        $request = Request::create($route, 'PUT', Input::all());
        $response = Route::dispatch($request);

        return $response;
    }

    /**
     * @param $route
     * @param array $params
     *
     * @return mixed
     */
    public function parseRoute($route, $params = [])
    {
        if (Route::has($route)) {
            $url = route($route, $params);
        } else {
            $url = url($route, $params);
        }
        $tld = ['.ved/', '.dev/', '.com/', '.org/', '.net/', '.int/', '.edu/', '.gov/', '.mil/', '.ac/', '.ad/', '.ae/', '.af/', '.ag/', '.ai/', '.al/', '.am/', '.an/', '.ao/', '.aq/', '.ar/', '.as/', '.at/', '.au/', '.aw/', '.ax/', '.az/', '.ba/', '.bb/', '.bd/', '.be/', '.bf/', '.bg/', '.bh/', '.bi/', '.bj/', '.bm/', '.bn/', '.bo/', '.bq/', '.br/', '.bs/', '.bt/', '.bv/', '.bw/', '.by/', '.bz/', '.ca/', '.cc/', '.cd/', '.cf/', '.cg/', '.ch/', '.ci/', '.ck/', '.cl/', '.cm/', '.cn/', '.co/', '.cr/', '.cs/', '.cu/', '.cv/', '.cw/', '.cx/', '.cy/', '.cz/', '.dd/', '.de/', '.dj/', '.dk/', '.dm/', '.do/', '.dz/', '.ec/', '.ee/', '.eg/', '.eh/', '.er/', '.es/', '.et/', '.eu/', '.fi/', '.fj/', '.fk/', '.fm/', '.fo/', '.fr/', '.ga/', '.gb/', '.gd/', '.ge/', '.gf/', '.gg/', '.gh/', '.gi/', '.gl/', '.gm/', '.gn/', '.gp/', '.gq/', '.gr/', '.gs/', '.gt/', '.gu/', '.gw/', '.gy/', '.hk/', '.hm/', '.hn/', '.hr/', '.ht/', '.hu/', '.id/', '.ie/', '.il/', '.im/', '.in/', '.io/', '.iq/', '.ir/', '.is/', '.it/', '.je/', '.jm/', '.jo/', '.jp/', '.ke/', '.kg/', '.kh/', '.ki/', '.km/', '.kn/', '.kp/', '.kr/', '.krd/', '.kw/', '.ky/', '.kz/', '.la/', '.lb/', '.lc/', '.li/', '.lk/', '.lr/', '.ls/', '.lt/', '.lu/', '.lv/', '.ly/', '.ma/', '.mc/', '.md/', '.me/', '.mg/', '.mh/', '.mk/', '.ml/', '.mm/', '.mn/', '.mo/', '.mp/', '.mq/', '.mr/', '.ms/', '.mt/', '.mu/', '.mv/', '.mw/', '.mx/', '.my/', '.mz/', '.na/', '.nc/', '.ne/', '.nf/', '.ng/', '.ni/', '.nl/', '.no/', '.np/', '.nr/', '.nu/', '.nz/', '.om/', '.pa/', '.pe/', '.pf/', '.pg/', '.ph/', '.pk/', '.pl/', '.pm/', '.pn/', '.pr/', '.ps/', '.pt/', '.pw/', '.py/', '.qa/', '.re/', '.ro/', '.rs/', '.ru/', '.rw/', '.sa/', '.sb/', '.sc/', '.sd/', '.se/', '.sg/', '.sh/', '.si/', '.sj/', '.sk/', '.sl/', '.sm/', '.sn/', '.so/', '.sr/', '.ss/', '.st/', '.su/', '.sv/', '.sx/', '.sy/', '.sz/', '.tc/', '.td/', '.tf/', '.tg/', '.th/', '.tj/', '.tk/', '.tl/', '.tm/', '.tn/', '.to/', '.tp/', '.tr/', '.tt/', '.tv/', '.tw/', '.tz/', '.ua/', '.ug/', '.uk/', '.us/', '.uy/', '.uz/', '.va/', '.vc/', '.ve/', '.vg/', '.vi/', '.vn/', '.vu/', '.wf/', '.ws/', '.ye/', '.yt/', '.yu/', '.za/', '.zm/', '.zr/', '.zw/', '.academy/', '.accountant/', '.accountants/', '.active/', '.actor/', '.adult/', '.aero/', '.agency/', '.airforce/', '.apartments/', '.app/', '.archi/', '.army/', '.associates/', '.attorney/', '.auction/', '.audio/', '.autos/', '.band/', '.bar/', '.bargains/', '.beer/', '.best/', '.bid/', '.bike/', '.bingo/', '.bio/', '.biz/', '.black/', '.blackfriday/', '.blog/', '.blue/', '.boo/', '.boutique/', '.build/', '.builders/', '.business/', '.buzz/', '.cab/', '.camera/', '.camp/', '.cancerresearch/', '.capital/', '.cards/', '.care/', '.career/', '.careers/', '.cash/', '.casino/', '.catering/', '.center/', '.ceo/', '.channel/', '.chat/', '.cheap/', '.christmas/', '.church/', '.city/', '.claims/', '.cleaning/', '.click/', '.clinic/', '.clothing/', '.club/', '.coach/', '.codes/', '.coffee/', '.college/', '.community/', '.company/', '.computer/', '.condos/', '.construction/', '.consulting/', '.contractors/', '.cooking/', '.cool/', '.country/', '.coupons/', '.credit/', '.creditcard/', '.cricket/', '.cruises/', '.dad/', '.dance/', '.date/', '.dating/', '.day/', '.deals/', '.degree/', '.delivery/', '.democrat/', '.dental/', '.dentist/', '.design/', '.diamonds/', '.diet/', '.digital/', '.direct/', '.directory/', '.discount/', '.dog/', '.domains/', '.download/', '.eat/', '.education/', '.email/', '.energy/', '.engineer/', '.engineering/', '.equipment/', '.esq/', '.estate/', '.events/', '.exchange/', '.expert/', '.exposed/', '.express/', '.fail/', '.faith/', '.fans/', '.farm/', '.fashion/', '.feedback/', '.finance/', '.financial/', '.fish/', '.fishing/', '.fit/', '.fitness/', '.flights/', '.florist/', '.flowers/', '.fly/', '.foo/', '.football/', '.forsale/', '.foundation/', '.fund/', '.furniture/', '.fyi/', '.gallery/', '.garden/', '.gift/', '.gifts/', '.gives/', '.glass/', '.global/', '.gold/', '.golf/', '.gop/', '.graphics/', '.green/', '.gripe/', '.guide/', '.guitars/', '.guru/', '.healthcare/', '.help/', '.here/', '.hiphop/', '.hiv/', '.hockey/', '.holdings/', '.holiday/', '.homes/', '.horse/', '.host/', '.hosting/', '.house/', '.how/', '.info/', '.ing/', '.ink/', '.institute[58]/', '.insure/', '.international/', '.investments/', '.jewelry/', '.jobs/', '.kim/', '.kitchen/', '.land/', '.lawyer/', '.lease/', '.legal/', '.lgbt/', '.life/', '.lighting/', '.limited/', '.limo/', '.link/', '.loan/', '.loans/', '.lol/', '.lotto/', '.love/', '.luxe/', '.luxury/', '.management/', '.market/', '.marketing/', '.markets/', '.mba/', '.media/', '.meet/', '.meme/', '.memorial/', '.men/', '.menu/', '.mobi/', '.moe/', '.money/', '.mortgage/', '.motorcycles/', '.mov/', '.movie/', '.museum/', '.name/', '.navy/', '.network/', '.new/', '.news/', '.ngo/', '.ninja/', '.one/', '.ong/', '.onl/', '.online/', '.ooo/', '.organic/', '.partners/', '.parts/', '.party/', '.pharmacy/', '.photo/', '.photography/', '.photos/', '.physio/', '.pics/', '.pictures/', '.pink/', '.pizza/', '.place/', '.plumbing/', '.plus/', '.poker/', '.porn/', '.post/', '.press/', '.pro/', '.productions/', '.prof/', '.properties/', '.property/', '.qpon/', '.racing/', '.recipes/', '.red/', '.rehab/', '.ren/', '.rent/', '.rentals/', '.repair/', '.report/', '.republican/', '.rest/', '.review/', '.reviews/', '.rich/', '.rip/', '.rocks/', '.rodeo/', '.rsvp/', '.run/', '.sale/', '.school/', '.science/', '.services/', '.sex/', '.sexy/', '.shoes/', '.show/', '.singles/', '.site/', '.soccer/', '.social/', '.software/', '.solar/', '.solutions/', '.space/', '.studio/', '.style/', '.sucks/', '.supplies/', '.supply/', '.support/', '.surf/', '.surgery/', '.systems/', '.tattoo/', '.tax/', '.taxi/', '.team/', '.technology/', '.tel/', '.tennis/', '.theater/', '.tips/', '.tires/', '.today/', '.tools/', '.top/', '.tours/', '.town/', '.toys/', '.trade/', '.training/', '.travel/', '.university/', '.vacations/', '.vet/', '.video/', '.villas/', '.vision/', '.vodka/', '.vote/', '.voting/', '.voyage/', '.wang/', '.watch/', '.webcam/', '.website/', '.wed/', '.wedding/', '.whoswho/', '.wiki/', '.win/', '.work/', '.works/', '.world/', '.wtf/', '.xxx/', '.xyz/', '.yoga/', '.zone/', '.maison/', '.abogado/', '.gratis/', '.futbol/', '.juegos/', '.soy/', '.tienda/', '.uno/', '.viajes/', '.haus/', '.immobilien/', '.jetzt/', '.kaufen/', '.reise/', '.reisen/', '.schule/', '.versicherung/', '.desi/', '.shiksha/', '.casa/', '.cafe/', '.immo/', '.moda/', '.voto/', '.bar/', '.bank/', '.coop/', '.enterprises/', '.industries/', '.institute/', '.ltda/', '.pub/', '.realtor/', '.reit/', '.rest/', '.restaurant/', '.sarl/', '.ventures/', '.capetown/', '.durban/', '.joburg/', '.asia/', '.krd/', '.nagoya/', '.okinawa/', '.ryukyu/', '.taipei/', '.tatar/', '.tokyo/', '.yokohama/', '.alsace/', '.amsterdam/', '.barcelona/', '.bayern/', '.berlin/', '.brussels/', '.budapest/', '.bzh/', '.cat/', '.cologne/', '.cymru/', '.eus/', '.frl/', '.gal/', '.gent/', '.hamburg/', '.irish/', '.koeln/', '.london/', '.madrid/', '.moscow/', '.nrw/', '.paris/', '.ruhr/', '.saarland/', '.scot/', '.tirol/', '.vlaanderen/', '.wales/', '.wien/', '.zuerich/', '.miami/', '.nyc/', '.quebec/', '.vegas/', '.kiwi/', '.melbourne/', '.sydney/', '.lat/', '.rio/', '.allfinanz/', '.android/', '.aquarelle/', '.axa/', '.barclays/', '.barclaycard/', '.bloomberg/', '.bmw/', '.bnl/', '.bnpparibas/', '.cal/', '.caravan/', '.cern/', '.chrome/', '.citic/', '.crs/', '.cuisinella/', '.dnp/', '.dvag/', '.emerck/', '.everbank/', '.firmdale/', '.flsmidth/', '.frogans/', '.gbiz/', '.gle/', '.globo/', '.gmail/', '.gmo/', '.gmx/', '.google/', '.hsbc/', '.ibm/', '.kred/', '.lacaixa/', '.latrobe/', '.lds/', '.mango/', '.mini/', '.monash/', '.mormon/', '.neustar/', '.nexus/', '.nhk/', '.nico/', '.nra/', '.otsuka/', '.ovh/', '.piaget/', '.pohl/', '.praxi/', '.prod/', '.sca/', '.scb/', '.schmidt/', '.sohu/', '.spiegel/', '.suzuki/', '.tui/', '.uol/', '.williamhill/', '.wme/', '.wtc/', '.yandex/', '.youtube/'];
        $explode = $this->explodeWhere($url, $tld);
        return end($explode).'/';
    }

    /**
     * @param $string
     * @param $array
     *
     * @return array
     */
    private function explodeWhere($string, $array)
    {
        $returnable = [];
        foreach ($array as $a) {
            if (stripos($string, $a) !== false) {
                $returnable = array_merge($returnable, explode($a, $string));
            }
        }

        return $returnable;
    }

    protected function emptyModel($request)
    {
        if ($request->ajax()) {
            return response()->json(['message' => 'No resource found!', 'code' => 404], 404);
        }
        return response(redirect('404'), 404);
    }
}

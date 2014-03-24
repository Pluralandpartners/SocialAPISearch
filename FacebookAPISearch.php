<?
/**
* Facebook-SocialAPISearch
*
* PHP version 5.3.10
*
* @package  SocialAPISearch
* @author Nicolai Pefaur at PluralAndPartners <nicolai@zunboy.com>
* @license Apache V2 License
* @link https://github.com/Pluralandpartners/SocialAPISearch/
* @API Rate Limiting https://dev.twitter.com/docs/rate-limiting/1.1
*/

class FacebookAPISearch
{
	private $hashtag;
	private $since_id;
	private $feeds = array();
	protected $access_token;

	public function __construct(array $settings, array $query)
    {
     	if (!isset($settings['app_id'])
            || !isset($settings['app_secret']))
           
        {
            throw new Exception('Make sure you are passing in the correct parameters');
        }
        
        if (!isset($query['hashtag'])
            || !isset($query['since_id']))
        {
            throw new Exception('Make sure you are passing in the correct parameters');
        }
        
        $this->hashtag = str_replace("#", "%23", $query['hashtag']);
        $this->since_id = $query['since_id'];
        $this->access_token = $settings['app_id'].'|'.$settings['app_secret'];

	}

	public function getFeeds()
	{
		if (!isset($this->feeds))
        	{
            		throw new Exception('Empty feeds');
		 }
        
		$url = 'https://graph.facebook.com/search';
		$getfield = $url.'?q='.$this->hashtag.'&type=post&access_token='.$this->access_token.'&limit=100&since='.$this->since_id;
	
		do{

			$json = file_get_contents($getfield);
			$response = json_decode($json, true);
			$data = $response['data'];
		
			
			if(isset($response['paging']['next']))
			{
				$getfield = $response['paging']['next'].'&since='.$this->since_id;
			}else{
				
				$getfield = '';
			}
			
			for ($i = 0; $i < count($data); $i++) 
			{
				if($this->postValidate($data, $i))
				{
					$this->feeds['created_at'][] 		= 	$this->setDateTime($data_fb[$i]{'created_time'});
					$this->feeds['user'][] 			= 	$data[$i]{'from'}{'name'};
					$this->feeds['uid'][] 			= 	$data[$i]{'from'}{'id'};
					$this->feeds['profile_image_url'][] 	= 	'http://graph.facebook.com/'.$data[$i]{'from'}{'id'}.'/picture?width=48&height=48';
					$this->feeds['text'][] 			= 	$data[$i]{'message'};
					$this->feeds['since_id'][] 		= 	strtotime($this->setDateTime($data[$i]{'created_time'}));
				}
			}
		
		}while($getfield != '');
		
		return $this->feeds;
	}
	
	public function getFeedsLenght()
	{
		if (!is_null($this->feeds))
        {
            throw new Exception('Empty feeds');
        }
		return count($this->feeds);
	}
	
	
	private function postValidate($data, $i)
	{
		if(isset($data[$i]{'created_time'}) 
			&& isset($data[$i]{'message'}) 
			&& isset($data[$i]{'from'}{'name'}) 
			&& isset($data[$i]{'from'}{'id'})) 
		{
			return true;
		}
		else return false;
	}
	
	private function setDateTime($tw_date)
	{
		return date( 'Y-m-d H:i:s', strtotime($tw_date));
	}

}

?>

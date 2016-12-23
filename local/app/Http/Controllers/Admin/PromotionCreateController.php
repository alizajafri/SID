<?php namespace App\Http\Controllers\Admin;
use App\User; 
use App\Promotion;
use App\PromotionAdtext;
use App\PromotionAdd;
use App\Category;
use App\Product;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Input;
use Auth;
use App\Http\Controllers\Controller;
use Validator;
use Session;
use Request;
use File;
use Response;
use Image;
use App\PramotionPromoBanner;
class PromotionCreateController extends Controller
{      
            public function __construct()
        {
            $this->middleware('auth');
            

        }
        public function index(){
            return view('admin/promotion')->with('title','Pramotion');
        }
        public function getcampaign(){
            $current=Carbon::now();
            $campaign = DB::table('promotion_adtext')->select('id','compaign_name')->where('end_date','>',$current)->get();
            return $campaign;
        }
        
        public function get_copy_campaign(){
            $current= Carbon::now();
            $cpy_campaign = DB::table('promotion_adtext')->select('id','compaign_name')->where('end_date','<',$current)->get();
            return $cpy_campaign;
        }

         public function change_catpro($catpro){
		 if($catpro=='category'){
		    $cat = DB::table('categorys')->select('*')->where('is_delete','0')->where('status','Active')->get();
            foreach($cat  as $val){
			 $list[]=array('id'=>$val->id,'name'=>$val->category_name);			
			} 
			return $list;          
		 }else if($catpro=='product')
		 {
		    $pro = DB::table('product')->select('*')->where('is_delete','0')->where('status','Active')->get();
            foreach($pro  as $val){
			 $list[]=array('id'=>$val->id,'name'=>$val->pro_name);			
			} 
			return $list; 		    
		 }else{
		    $store = DB::table('store')->select('*')->where('store_status','Active')->get();
            foreach($store  as $val){
			 $list[]=array('id'=>$val->id,'name'=>$val->store_name);			
			} 
			return $list; 	
		 }
           
        } 
        
        public function get_updcamp_rec($id){
            $list=array();
            $cat_arr=array();
            $camp_upd_rec = DB::table('promotion_adtext')                             
                             ->where('promotion_adtext.id',$id)
                            ->first(); 
           
            $adtype=$camp_upd_rec->ad_type;
         //   $cat_id=$camp_upd_rec->destination_cat;
        //    $cat_val_arr=explode(",", $cat_id);
		if($adtype=="text_ad"){
        $create_package = DB::table('promotion_settings')->where('field_name','create_package')->where('status',1)->where('ad_type',$adtype)->get();
			}else{
		$create_package = DB::table('promotion_settings')->where('field_name',$camp_upd_rec->ad_placement)->where('status',1)->where('ad_type',$adtype)->get();
			}
            $schedule_status = DB::table('promotion_settings')->where('field_name','schedule_status')->where('ad_type',$adtype)->get();
       //     $destination_cat = DB::table('promotion_settings')->where('field_name','destination_cat')->where('ad_type',$adtype)->get();            
              $categ_name = DB::table('categorys')->where('status','Active')->where('is_delete',0)->get();
              $prod_name = DB::table('product')->where('status','Active')->where('is_delete',0)->get();
//             foreach($cat_val_arr as $val){
//              $cat_arr[]=DB::table('categorys')->where('id',$val)->first();
//            } 
            foreach($create_package as $key=>$crt_pack){
               $fild_val=$crt_pack->field_value;               
               $new_array = explode('-',$fild_val);              
               $create_package[$key]->nview=$new_array[0];
               $create_package[$key]->price=$new_array[1];
            }
            
            
            $list['updrec']=$camp_upd_rec;
            $list['create_pakg']=$create_package;
            $list['schedule']=$schedule_status;
          //  $list['destination']=$destination_cat;
          $list['all_cat']= $categ_name;
         //   $list['carbn']= $current ;
          $list['product']=$prod_name;
		  //print_r( $list);
            return   $list;
        }


        public function get_promotn($id){
		   $camp = DB::table('promotion_adtext')->select('*')->where('id',$id)->first();
		   if($camp->ad_type=="text_ad"){
		   $promotn = DB::table('pramotion_promo_add')->select('id','promotion_name')->where('campaign_id',$id)->get();
		   }else{
		   $promotn = DB::table('pramotion_promo_banner')->select('id','promotion_name')->where('campaign_id',$id)->get();
		   }
           
            return $promotn; 
        }
        
          public function get_copy_promotn(){
            $current= Carbon::now();
            $cpy_promon = DB::table('pramotion_promo_add')
                                ->select('pramotion_promo_add.id','promotion_name')
                                ->leftJoin('promotion_adtext', 'pramotion_promo_add.campaign_id', '=', 'promotion_adtext.id')
                                ->where('promotion_adtext.end_date','<',$current)
                                ->get();
            return $cpy_promon;
        } 
        
        public function get_promo_rec($id){
            
            $list=array();
            $cat_arr=array();
            $camp_upd_rec = DB::table('pramotion_promo_add')
                             ->where('pramotion_promo_add.id',$id)
                            ->first(); 
            $cat_id=$camp_upd_rec->destination_cat;
            $prod_id=$camp_upd_rec->product_promote;
            $cat_val_arr=explode(",", $cat_id);
              $prod_name = DB::table('product')->where('id',$prod_id)->where('status','Active')->where('is_delete',0)->first();
            foreach($cat_val_arr as $val){
              $cat_arr[]=DB::table('categorys')->where('id',$val)->first();
            }
            $list['promo_rec']=$camp_upd_rec;    
            $list['selected_cat']= $cat_arr;
            $list['product']=$prod_name;
            return   $list;
            
        }

      


        public function get_camp_preview($id){
         
          $cat_arr=array();        
          $previw_rec =DB::table('pramotion_promo_add')
                        ->where('pramotion_promo_add.id',$id)
                        ->first();          
          $prod_id=$previw_rec->product_promote;
          $cat_id=$previw_rec->destination_cat;
          $cat_val_arr=explode(",", $cat_id);
          foreach($cat_val_arr as $val){
              $cat_arr[]=DB::table('categorys')->where('id',$val)->first();
          }          
          $prod = DB::table('product')->where('id',$prod_id)->first();          
          $rec=array(              
                'prviw_data'=>$previw_rec,
                 'product'=>$prod,
                 'category'=>$cat_arr 
                  );
          return $rec; 
        }
        
		public function placement($placement){
	
            $create_package = DB::table('promotion_settings')->where('field_name',$placement)->where('status',1)->where('ad_type','banner_ad')->get();
    
            foreach($create_package as $key=>$crt_pack){
               $fild_val=$crt_pack->field_value;               
               $new_array = explode('-',$fild_val);              
               $create_package[$key]->nview=$new_array[0];
               $create_package[$key]->price=$new_array[1];
            }
            
             $promo_seting_rec=array(
            
                'create_package'=> $create_package,
            
            );  
             
            return $promo_seting_rec; 
		}
        public function getpromotion($adtype){
            $promot_all = DB::table('promotion_settings')->whereNotIn('field_name', ['create_package','schedule_status','destination_cat','payment_option','tooltip','seller_selection','placement_pkg'])->where('ad_type',$adtype)->get();
            $create_package = DB::table('promotion_settings')->where('field_name','create_package')->where('status',1)->where('ad_type',$adtype)->get();
            $schedule_status = DB::table('promotion_settings')->where('field_name','schedule_status')->where('ad_type',$adtype)->get();
            $destination_cat = DB::table('promotion_settings')->where('field_name','destination_cat')->where('ad_type',$adtype)->get();            
            $selr_selection = DB::table('promotion_settings')->where('field_name','seller_selection')->where('ad_type',$adtype)->get();
            $placemnt_pakg = DB::table('promotion_settings')->where('field_name','placement_pkg')->where('ad_type',$adtype)->get();
            $tooltip = DB::table('promotion_settings')->where('field_name','tooltip')->where('ad_type',$adtype)->get();
            $categ_name = DB::table('categorys')->where('status','Active')->where('is_delete',0)->get();
            $prod_name = DB::table('product')->where('status','Active')->where('is_delete',0)->get();
            foreach($create_package as $key=>$crt_pack){
               $fild_val=$crt_pack->field_value;               
               $new_array = explode('-',$fild_val);              
               $create_package[$key]->nview=$new_array[0];
               $create_package[$key]->price=$new_array[1];
            }
            
             $promo_seting_rec=array(
                'prom_all'=>$promot_all,
                'create_package'=> $create_package,
                'schedule_status'=>$schedule_status,
                'destination_cat'=>$destination_cat,                
                'seler_select'=>$selr_selection,
                'placemnt_pkg'=>$placemnt_pakg,
                'tooltip'=>$tooltip,
                'category_name'=>$categ_name,
                'product_name'=>$prod_name
            );  
             
            return $promo_seting_rec;  
        }
        
        public function save_promotion_adtext(){
          
            $prom_adtext=Request::input('adtext_data'); 
            
            if($prom_adtext['ad_type']=='Text Ad'){
               $prom_adtext['ad_type']='text_ad'; 
            }else{
			   $prom_adtext['ad_type']='banner_ad'; 
			}
              /**********************Insert Adtext**************************/           
            if(!array_key_exists('upd_camp', $prom_adtext)){
               $validator = Validator::make(Request::all(), [
               'adtext_data.newcamp' => 'required',                  
               'adtext_data.select_view'=>'required',            
               'adtext_data.schedule'=>'required',
               'adtext_data.ad_type'=>'required'
              
            ]);
               	   $friendly_names = array(
			'adtext_data.newcamp' => 'Campaign Name',
			'adtext_data.select_view' => 'Views per product',
			'adtext_data.schedule' => 'Schedule',
			'adtext_data.ad_type' => 'Ad Type',			
			
		    );
	$validator->setAttributeNames($friendly_names);
             if ($validator->fails()) {
                              $list[]='error';
                              $msg=$validator->errors()->all();
			      $list[]=$msg;
			      return $list;
              }
            
            if ((!array_key_exists('id', $prom_adtext)) || ($prom_adtext['id']=='') || ($prom_adtext['campain']=='copy_campn')) {
                if(($prom_adtext['schedule']==9) || ($prom_adtext['schedule']==41)){
                    $start_date=$prom_adtext['start_date'];
                    $end_date=$prom_adtext['end_date'];
                }
                if(($prom_adtext['schedule']==4) || ($prom_adtext['schedule']==36))
                {
                    $current=Carbon::now();
                    $start_date=Carbon::now();
                    $end_date=$current->addDays(7);   
                }
                 if(($prom_adtext['schedule']==5) || ($prom_adtext['schedule']==37) )
                {
                    $current=Carbon::now();
                    $start_date=Carbon::now();
                    $end_date=$current->addDays(15);   
                }
                 if(($prom_adtext['schedule']==6) || ($prom_adtext['schedule']==38))
                {
                    $current=Carbon::now();
                    $start_date=Carbon::now();
                    $end_date=$current->addDays(30);   
                }
                  if(($prom_adtext['schedule']==7) || ($prom_adtext['schedule']==39))
                {
                    $current=Carbon::now();
                    $start_date=Carbon::now();
                    $end_date=$current->addDays(60);   
                }
                 if(($prom_adtext['schedule']==8)  || ($prom_adtext['schedule']==40))
                {
                    $current=Carbon::now();
                    $start_date=Carbon::now();
                    $end_date=$current->addDays(90);   
                }
                  $adtext = new PromotionAdtext;
                  $adtext->compaign_name = $prom_adtext['newcamp'];
                  $adtext->ad_type=$prom_adtext['ad_type'];
				  if($prom_adtext['ad_type'] == 'banner_ad'){
				  $adtext->ad_placement=$prom_adtext['ad_placement'];
				  }
                  $adtext->view_price = $prom_adtext['select_view'];
                  $adtext->schedule = $prom_adtext['schedule'];
                  $adtext->start_date = $start_date;
                  $adtext->end_date = $end_date;
                  $adtext->save();  
                  $list[] =  'success';
                  $list[] =  'Record is added successfully.';
                  $list[] =  $adtext->id;
                
            } else{
                
                 if(($prom_adtext['schedule']==9) || ($prom_adtext['schedule']==41)){
                    $start_date=$prom_adtext['start_date'];
                    $end_date=$prom_adtext['end_date'];
                }
                if(($prom_adtext['schedule']==4) || ($prom_adtext['schedule']==36))
                {
                    $current=Carbon::now();
                    $start_date=Carbon::now();
                    $end_date=$current->addDays(7);   
                }
                 if(($prom_adtext['schedule']==5) || ($prom_adtext['schedule']==37))
                {
                    $current=Carbon::now();
                    $start_date=Carbon::now();
                    $end_date=$current->addDays(15);   
                }
                 if(($prom_adtext['schedule']==6) || ($prom_adtext['schedule']==38))
                {
                    $current=Carbon::now();
                    $start_date=Carbon::now();
                    $end_date=$current->addDays(30);   
                }
                  if(($prom_adtext['schedule']==7) || ($prom_adtext['schedule']==39))
                {
                    $current=Carbon::now();
                    $start_date=Carbon::now();
                    $end_date=$current->addDays(60);   
                }
                 if(($prom_adtext['schedule']==8)  || ($prom_adtext['schedule']==40))
                {
                    $current=Carbon::now();
                    $start_date=Carbon::now();
                    $end_date=$current->addDays(90);   
                }
                
                  $adtext = PromotionAdtext::find($prom_adtext['id']);                
                  $adtext->compaign_name = $prom_adtext['newcamp'];
                  $adtext->ad_type = $prom_adtext['ad_type'];  
				  if($prom_adtext['ad_type'] == 'banner_ad'){
				  $adtext->ad_placement=$prom_adtext['ad_placement'];
				  }                
                  $adtext->view_price = $prom_adtext['select_view'];
                  $adtext->schedule = $prom_adtext['schedule'];
                  $adtext->start_date = $start_date;
                  $adtext->end_date = $end_date;
                  $adtext->save(); 
                  $list[] =  'success';
                  $list[] =  'Record is updated successfully.';
                  $list[] =  $adtext->id;
                 
                
                
            }
          }  
            /************************Update Ad text********************************/
             if(array_key_exists('upd_camp', $prom_adtext)){
               $validator = Validator::make(Request::all(), [
               'adtext_data.upd_camp' => 'required',                  
               'adtext_data.select_view'=>'required',            
               'adtext_data.schedule'=>'required',
               'adtext_data.ad_type'=>'required'
              
            ]);
               	   $friendly_names = array(
			'adtext_data.upd_camp' => 'Campaign Name',
			'adtext_data.select_view' => 'Views per product',
			'adtext_data.schedule' => 'Schedule',
			'adtext_data.ad_type' => 'Ad Type',
			
			
		    );
	$validator->setAttributeNames($friendly_names);
             if ($validator->fails()) {
                              $list[]='error';
                              $msg=$validator->errors()->all();
			      $list[]=$msg;
			      return $list;
              }
              
                $adtext = PromotionAdtext::find($prom_adtext['id']);  
                 if($prom_adtext['ad_type'] == 'banner_ad'){
				  $adtext->ad_placement=$prom_adtext['ad_placement'];
				  }   
                  $adtext->view_price = $prom_adtext['select_view'];
                 
                  $adtext->save(); 
                  $list[] =  'success';
                  $list[] =  'Record is updated successfully.';
                  $list[] =  $adtext->id;
                

          }
            
            return $list;
        }
        
        public function insert_promotion_adtext(){
		    $val=Request::input('adtext_data');  
            
            /******************Insert record**********************/
            if(!array_key_exists('upd_promot',$val)){
			//print_r($val);
			if($val['ad_type']=="Banner Ad" || $val['ad_type']=="banner_ad"){
			  $validator = Validator::make(Request::all(), [
               'adtext_data.newpromot' => 'required',	       
               'adtext_data.catprosto'=>'required', 
			   'adtext_data.val_cps'=>'required',           
               'adtext_data.banner_img'=>'required',
              
              
            ]);
               	   $friendly_names = array(
		     	     'adtext_data.newpromot' => 'Promotion Name',	       
                     'adtext_data.catprosto'=>'Select Store/Product/Category', 
			         'adtext_data.val_cps'=>'Store/Product/Category',           
                     'adtext_data.banner_img'=>'Banner',
			
		    );
	       $validator->setAttributeNames($friendly_names);
             if ($validator->fails()) {
                              $list[]='error';
                              $msg=$validator->errors()->all();
			      $list[]=$msg;
			      return $list;
              }
            
           
            
            $promotion =new PramotionPromoBanner;         
            $promotion->promotion_name = $val['newpromot']; 
            $promotion->campaign_id = $val['upd_camp'];    
            $promotion->type = $val['catprosto'];     
            $promotion->type_id =$val['val_cps']; 
            $promotion->banner= $val['banner_img'];                 
            $promotion->save();
            $list[] =  'success';
            $list[] =  'Record is updated successfully.';
            $list[] =  $promotion->id;
            return $list;
			}
			else{
               $validator = Validator::make(Request::all(), [
               'adtext_data.newpromot' => 'required',	       
               'adtext_data.product'=>'required',            
               'adtext_data.category'=>'required',
               'adtext_data.add_content'=>'required',
                'adtext_data.add_discrip'=>'required'   
              
            ]);
               	   $friendly_names = array(
			'adtext_data.newpromot' => 'Promotion Name',	       
                        'adtext_data.product'=>'Product Name',            
                        'adtext_data.category'=>'Category Name',
                        'adtext_data.add_content'=>'Content',
                        'adtext_data.add_discrip'=>'Description'  
			
		    );
	       $validator->setAttributeNames($friendly_names);
             if ($validator->fails()) {
                              $list[]='error';
                              $msg=$validator->errors()->all();
			      $list[]=$msg;
			      return $list;
              }
            
            if(count($val['category'])>0){
                $cat_val="";
                foreach ($val['category'] as $cat){
                    if($cat_val!=''){
                    $cat_val=$cat_val.','.$cat;
                    }else{
                       $cat_val=$cat; 
                    }
                }
            }
            
            $promotion =new PromotionAdd;         
            $promotion->promotion_name = $val['newpromot']; 
            $promotion->campaign_id = $val['upd_camp'];    
            $promotion->product_promote = $val['product'];     
            $promotion->destination_cat = $cat_val; 
            $promotion->adcontent_title= $val['add_content'];
            $promotion->adcontent_discrip = $val['add_discrip'];             
            $promotion->save();
            $list[] =  'success';
            $list[] =  'Record is updated successfully.';
            $list[] =  $promotion->id;
            return $list;
            
             }
			 }
            /******************Update record**********************/
           
             if(array_key_exists('upd_promot', $val)){
                 
               $validator = Validator::make(Request::all(), [
               'adtext_data.upd_promot' => 'required',	       
               'adtext_data.product'=>'required',            
               'adtext_data.category'=>'required',
               'adtext_data.add_content'=>'required',
                'adtext_data.add_discrip'=>'required'   
              
            ]);
               	   $friendly_names = array(
			'adtext_data.upd_promot' => 'Promotion Name',	       
                        'adtext_data.product'=>'Product Name',            
                        'adtext_data.category'=>'Category Name',
                        'adtext_data.add_content'=>'Content',
                        'adtext_data.add_discrip'=>'Description'  
			
		    );
	$validator->setAttributeNames($friendly_names);
             if ($validator->fails()) {
                              $list[]='error';
                              $msg=$validator->errors()->all();
			      $list[]=$msg;
			      return $list;
              }
            
            if(count($val['category'])>0){
                $cat_val="";
                foreach ($val['category'] as $cat){
                    if($cat_val!=''){
                    $cat_val=$cat_val.','.$cat;
                    }else{
                       $cat_val=$cat; 
                    }
                }
            }
            
            $promotion =PromotionAdd::find($val['upd_promot']);      
             
            $promotion->campaign_id = $val['upd_camp'];    
            $promotion->product_promote = $val['product'];     
            $promotion->destination_cat = $cat_val; 
            $promotion->adcontent_title= $val['add_content'];
            $promotion->adcontent_discrip = $val['add_discrip'];             
            $promotion->save();
            $list[] =  'success';
            $list[] =  'Record is updated successfully.';
            $list[] =  $promotion->id;
            return $list;
            
           } 
            
        }
        
         public function update_adbanr()
       {
         $def = Request::input('def');
	     $promo_ad = DB::table('promotion_adtext')->where('id',$def)->first();
		// print_r($promo_ad->ad_placement);
		 if($promo_ad->ad_placement=='home_top_bot_banner')
		 {
		 $set='home_topbot_banr_setting';
		 }elseif($promo_ad->ad_placement=='home_right_banner')
		 {
		 $set='home_right_banr_setting';
		 }
		 elseif($promo_ad->ad_placement=='categ_left_bot_banner')
		 {
		 $set='categ_lftbotm_banr_setting';
		 }
		 elseif($promo_ad->ad_placement=='prod_left_bot_banner')
		 {
		 $set='prod_leftbot_banr_setting';
		 }
		$promo_ad1 = DB::table('promotion_settings')->where('field_name',$set)->where('ad_type','banner_ad')->first();		
		 $weight_img=explode('-',$promo_ad1->field_value);
		 $dim_img=explode('x',$weight_img[0]);
        if(Request::input('folder'))
			$folder = '/'.Request::input('folder');
		    $image= Input::file('image');
		
		/*if(Request::input('width')&&Request::input('height'))
		{
		*/
		   $width=$dim_img[0];
		   $height=$dim_img[1];
			$image_info = getimagesize(Input::file('image'));
               $image_width = $image_info[0];
               $image_height = $image_info[1];
			  // print_r($image_width);print_r($dim_img);print_r($weight_img);echo 'size';print_r(File::size($image));echo 'ht';print_r($image_height);
			if($image_width > $dim_img[0] || $image_height >  $dim_img[1]  || File::size($image) > ($weight_img[1]*1000000))
			{
				$list[]='error';
				$msgs[]='Fix your image dimension('.$dim_img[0].'x'.$dim_img[1].') or size('.$weight_img[1].'MB).';
				$list[]=$msgs;
				return $list;
			}
			
		/*} */
		
		
            $destinationPath = 'uploads'.@$folder; // upload path
            $extension = Input::file('image')->getClientOriginalExtension(); // getting image extension
            if(($extension=='jpg') || ($extension=='jpeg') || ($extension=='png') ){
            $fileName = time().'.'.$extension; // renameing image 
			//thumb			
			 $path = ($destinationPath . '/thumb_'.$fileName);
			Image::make($image->getRealPath())->resize($width, $height)->save($path);
			//mid
			$path = ($destinationPath . '/mid_'.$fileName);
			Image::make($image->getRealPath())->resize($width, $height)->save($path);	
			
            Input::file('image')->move($destinationPath, $fileName); 
            return $fileName;
            }else{
                return false;
            }
        
       }
 
        
        
}       
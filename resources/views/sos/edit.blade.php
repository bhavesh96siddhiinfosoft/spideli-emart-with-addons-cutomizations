@extends('layouts.app')
<style>
.leaflet-routing-container{
display: none
}
</style>
@section('content')
	<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.sos')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <?php if (isset($_GET['eid']) && $_GET['eid'] != '') {?>
                    <li class="breadcrumb-item"><a href= "{{route('drivers.ride', $_GET['eid'])}}" >{{trans('lang.order_plural')}}</a></li>
                <?php } else { ?>
                    <li class="breadcrumb-item"><a href= "{!! route('sos') !!}" >{{trans('lang.sos')}}</a></li>
                <?php } ?>
                <li class="breadcrumb-item">{{trans('lang.edit_sos')}}</li>
            </ol>
        </div>
    </div>
<div class="container-fluid">
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body p-0 pb-5">
        <div class="user-detail" role="tabpanel">
          <div class="row">
            <div class="col-12">
									<div class="box">
										<div class="box-header bb-2 border-primary">
											<h3 class="box-title">{{trans('lang.map_view')}}</h3>
										</div>
										<div class="box-body">
											<div id="map" style="height:300px">
											</div>
										</div>
									</div>
              <div class="box">
              <div class="box-header bb-2 border-primary">
                <h3 class="box-title">{{trans('lang.general_details')}}</h3>
              </div>
              <div class="box-body">
						      <table class="table table-hover">
						            <thead>
						            <tr>
						            <th>{{trans('lang.date_created')}}</th>
												<th>{{trans('lang.status')}}</th>
						            <th></th>
						            </tr>
						            </thead>
						            <tbody>
						            <tr>
						            <td id="createdAt"></td>
												<td> <select id= "order_status" class="form-control">
                          <option value="Initiated" id="initiated">{{ trans('lang.initiated')}}</option>
                          <option value="Processing" id="processing">{{ trans('lang.processing')}}</option>
                          <option value="Completed" id="completed">{{ trans('lang.completed')}}</option>
                      </select></td>
                  <td><button type="button" class="btn btn-primary edit-form-btn" ><i class="fa fa-save"></i> {{trans('lang.update')}}</button></td>
						      </tr>
						      </tbody>
                </table>
						  </div>
                </div>
              <div class="box">
              <div class="box-header bb-2 border-primary">
              <h3 class="box-title">{{ trans('lang.billing_details')}}</h3>
              </div>
              <div class="box-body">
						      <table class="table table-hover">
						            <thead>
						            <tr>
						            <th>{{trans('lang.email_address')}}</th>
												<th>{{trans('lang.phone')}}</th>
						            </tr>
						            </thead>
						            <tbody>
						            <tr>
						            <td id="billing_email"></td>
												<td id="billing_phone"> </td>
						      </tr>
						      </tbody>
                </table>
						  </div> </div>
              <div class="box">
              <div class="box-header bb-2 border-primary">
              <h3 class="box-title">{{ trans('lang.ride_detail')}}</h3>
              </div>
              <div class="box-body">
						      <table class="table table-hover">
						            <thead>
						            <tr>
						            <th>{{trans('lang.order_user_id')}}</th>
												<th>{{trans('lang.driver_plural')}}</th>
                        <th>{{trans('lang.source')}}</th>
                        <th>{{trans('lang.destination_location')}}</th>
												<th>{{trans('lang.status')}}</th>
						            </tr>
						            </thead>
						            <tbody>
						            <tr>
						            <td id="client"></td>
												<td id="driver"> </td>
                        <td id="source_address"></td>
                        <td id="destination_address"></td>
												<td id="status"> </td>
						      </tr>
						      </tbody>
                </table>
						  </div>
              </div>
              <div class="box">
              <div class="box-header bb-2 border-primary">
              <h3 class="box-title">{{ trans('lang.driver_detail')}}</h3>
              </div>
              <div class="box-body">
						      <table class="table table-hover">
						            <thead>
						            <tr>
						            <th>{{trans('lang.image')}}</th>
												<th>{{trans('lang.driver_plural')}}</th>
                        <th>{{trans('lang.email_address')}}</th>
												<th>{{trans('lang.phone')}}</th>
						            </tr>
						            </thead>
						            <tbody>
						            <tr>
						            <td><img src="" class="resturant-img rounded-circle" alt="driver" width="70px" height="70px"></td>
												<td class="vendor-title"> </td>
                        <td id="vendor_email"></td>
												<td id="vendor_phone"> </td>
						      </tr>
						      </tbody>
                </table>
						  </div> </div>
              <div class="box">
              <div class="box-header bb-2 border-primary">
              <h3 class="box-title">{{ trans('lang.car_info')}}</h3>
              </div>
              <div class="box-body">
						      <table class="table table-hover">
						            <thead>
						            <tr>
						            <th>{{trans('lang.image')}}</th>
												<th>{{trans('lang.car_name')}}</th>
                        <th>{{trans('lang.car_number')}}</th>
												<th>{{trans('lang.car_make')}}</th>
						            </tr>
						            </thead>
						            <tbody>
						            <tr>
						            <td> <img src="" class="car-img rounded-circle" alt="car" width="70px" height="70px"></td>
												<td id="driver_carName"> </td>
                        <td id="driver_carNumber"></td>
												<td id="driver_car_make"> </td>
						      </tr>
						      </tbody>
                </table>
                </div> 
						            </div>
				            	</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
 @endsection
@section('style')
@section('scripts')

<script src="https://cdnjs.cloudflare.com/ajax/libs/printThis/1.15.0/printThis.js"></script>
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />

<script type="text/javascript">

var adminCommission=0;
var id_rendom = "<?php echo uniqid();?>";
var id = "<?php echo $id;?>";
var driverId='';
var fcmToken='';
var old_order_status='';
var payment_shared=false;
var deliveryChargeVal = 0;
var tip_amount_val = 0;
var tip_amount=0;
var vendorname='';
var database = firebase.firestore();
var ref = database.collection('SOS').where("id","==",id);
var append_procucts_list = '';
var append_procucts_total = '';
var total_price=0;
var currentCurrency = '';
var currencyAtRight = false;
var refCurrency = regionCurrencyRef();
var orderPreviousStatus = '';
var orderTakeAwayOption = false;
var manfcmTokenVendor='';
var manname='';
var mapType = 'ONLINE';
var sourceLocationName = '';
var destinationLocationName = '';

database.collection('settings').doc('DriverNearBy').get().then(async function (snapshots) {
    var data = snapshots.data();
    if (data && data.selectedMapType && data.selectedMapType == "osm") {
        mapType = "OFFLINE"
    }
});

refCurrency.get().then( async function(snapshots){  
    var currencyData = snapshots.docs[0].data();
    currentCurrency = currencyData.symbol;
    currencyAtRight = currencyData.symbolAtRight;
}); 

var geoFirestore = new GeoFirestore(database);
var place_image ='';
var ref_place = database.collection('settings').doc("placeHolderImage");
 ref_place.get().then( async function(snapshots){
    var placeHolderImage = snapshots.data();            
    place_image = placeHolderImage.image;   
});

$(document).ready(function(){

    var alovelaceDocumentRef = database.collection('vendor_orders').doc();
    if(alovelaceDocumentRef.id){
        id_rendom=alovelaceDocumentRef.id;
    }
    $(document.body).on('click', '.redirecttopage' ,function(){    
        var url=$(this).attr('data-url');
        window.location.href = url;
    });
  
    jQuery("#data-table_processing").show();
  
    ref.get().then( async function(snapshots){
      var ride = snapshots.docs[0].data();
      orderPreviousStatus = ride.status;
      if (ride.status) {
          orderPaymentMethod = ride.status;
      }
      $("#order_status option[value='"+ride.status+"']").attr("selected","selected");
      var price = 0;
    
      if (ride.orderId) {

        var driver = database.collection('rides').where("id","==",ride.orderId);
      
        driver.get().then( async function(snapshotsnew){

          var driverdata = snapshotsnew.docs[0].data();
          var originAddress = driverdata.sourceLocation.latitude+','+driverdata.sourceLocation.longitude;
          var sosAddress = ride.latLong.latitude+','+ ride.latLong.longitude;
          var destinationAddress= driverdata.destinationLocation.latitude+','+driverdata.destinationLocation.longitude;
          sourceLocationName = driverdata.sourceLocationName;
          destinationLocationName = driverdata.destinationLocationName;
          
          await InitializeMap();
          
          drawRoute(originAddress,sosAddress ,destinationAddress)

          if (driverdata.createdAt) {
            var date1 = driverdata.createdAt.toDate().toDateString();
            var date = new Date(date1);
            var dd = String(date.getDate()).padStart(2, '0');
            var mm = String(date.getMonth() + 1).padStart(2, '0'); //January is 0!
            var yyyy = date.getFullYear();
            var createdAt_val = yyyy + '-' + mm + '-' + dd;
            var time = driverdata.createdAt.toDate().toLocaleTimeString('en-US');
            $('#createdAt').text(createdAt_val+' '+time);
        }
          
          $("#billing_name").text(driverdata.author.shippingAddress.name);
          var billingAddressstring = '';
          $("#trackng_number").text(id);

        if(driverdata.author.shippingAddress.hasOwnProperty('line1')){
          $("#billing_line1").text(driverdata.author.shippingAddress.line1);
        }

        if(driverdata.author.shippingAddress.hasOwnProperty('line2')){
          billingAddressstring = billingAddressstring + driverdata.author.shippingAddress.line2; 
        }
        if(driverdata.author.shippingAddress.hasOwnProperty('city')){
          billingAddressstring = billingAddressstring+", "+ driverdata.author.shippingAddress.city; 
        }
        if(driverdata.author.shippingAddress.hasOwnProperty('postalCode')){
          billingAddressstring = billingAddressstring+", "+ driverdata.author.shippingAddress.postalCode; 
        }

        if(driverdata.author.hasOwnProperty('phoneNumber')){ 
          if(driverdata.author.phoneNumber.includes('+')){
              $("#billing_phone").text('+' + EditPhoneNumber(driverdata.author.phoneNumber.slice(1)));
          }else{
              $("#billing_phone").text(EditPhoneNumber(driverdata.author.phoneNumber));
          }
        }

        $("#billing_line2").text(billingAddressstring);  
        if(driverdata.author.shippingAddress.hasOwnProperty('country')){
          $("#billing_country").text(driverdata.author.shippingAddress.country); 
        }

        if(driverdata.author.hasOwnProperty('email')){
          $("#billing_email").html('<a href="mailto:'+driverdata.author.email+'">'+ shortEmail(driverdata.author.email) +'</a>'); 
        }

          if (driverdata.id) {
             var route_view =  '{{route("drivers.view", ":id")}}';
               route_view = route_view.replace(':id', driverdata.id);
             $('#resturant-view').attr('data-url',route_view);  
         }
         if (driverdata.author.firstName) {
             $('#client').text(driverdata.author.firstName);  
         }
         if (driverdata.driver.firstName) {
             $('#driver').text(driverdata.driver.firstName);  
         }
          if (driverdata.sourceLocationName) {
              $('#source_address').html(driverdata.sourceLocationName);  
          }
          if (driverdata.destinationLocationName) {
              $('#destination_address').html(driverdata.destinationLocationName);  
          }
         if (driverdata.status) {
             $('#status').text(driverdata.status);  
         }

      if (driverdata.driver.id) {
        
        var driver = database.collection('users').where("id","==",driverdata.driver.id);
        
        driver.get().then( async function(snapshotsnew){

                if(snapshotsnew.empty){
                    $('.resturant-img').attr('src',place_image); 
                    $('.vendor-title').html('-');  
                    $('#vendor_email').html('-');  
                    $('#vendor_phone').text('-'); 
                    $('.car-img').attr('src',place_image);
                    $('#driver_carName').html('-'); 
                    $('#driver_carNumber').html('-'); 
                    $('#driver_car_make').text('-'); 

                }else{
                  
                    var driver_data = snapshotsnew.docs[0].data();
                    if (driver_data.id) {
                        var route_view =  '{{route("drivers.view", ":id")}}';
                          route_view = route_view.replace(':id', driver_data.id);
                        $('#resturant-view').attr('data-url',route_view);  
                    }
                    if (driver_data.profilePictureURL) {
                        $('.resturant-img').attr('src',driver_data.profilePictureURL);  
                    }else{
                        $('.resturant-img').attr('src',place_image); 
                    }
                    if (driver_data.firstName) {
                        $('.vendor-title').html(driver_data.firstName+' '+driver_data.lastName);  
                    }
                    if (driver_data.email) {
                          $('#vendor_email').html(shortEmail(driver_data.email));  
                    }
                    if (driver_data.phoneNumber) {
                          if(driver_data.phoneNumber.includes('+')){
                              $('#vendor_phone').text('+' + EditPhoneNumber(driver_data.phoneNumber.slice(1)));
                          }else{
                              $('#vendor_phone').text(EditPhoneNumber(driver_data.phoneNumber));
                          }
                    }
                    if (driver_data.id) {
                        var route_view =  '{{route("drivers.view", ":id")}}';
                          route_view = route_view.replace(':id', driver_data.id);
                        $('#resturant-car').attr('data-url',route_view);  
                    }
                    if (driver_data.carPictureURL) {
                        $('.car-img').attr('src',driver_data.carPictureURL);  
                    }else{
                        $('.car-img').attr('src',place_image); 
                    }
                    if (driver_data.carName) {
                        $('#driver_carName').html(driver_data.carName);  
                    }
                    if (driver_data.carNumber) {
                          $('#driver_carNumber').html(driver_data.carNumber);  
                      }
                    if (driver_data.carMakes) {
                        $('#driver_car_make').text(driver_data.carMakes);  
                      }
                }

            });
          }
      });
    }
    
    jQuery("#data-table_processing").hide();
  })

    $(".edit-form-btn").click(async function(){
        var clientName = $(".client_name").val();
        var orderStatus = $("#order_status").val();
        if(old_order_status!=orderStatus){
          database.collection('SOS').doc(id).update({'status':orderStatus}).then( async function(result) {
              await $.ajax({
                type:'POST',
                url:"<?php echo route('order-status-notification'); ?>",
                data:{_token:'<?php echo csrf_token() ?>','orderStatus':orderStatus},
                success:function(data) {
                        window.location.href = '{{ route("sos")}}';
                }
              });
          }); 
        }
    })
})

var _mapPoints = new Array();    
var map, routingControl;

function InitializeMap() {  

  if (mapType == "OFFLINE"){
    map = L.map('map').setView([21.7679, 78.8718], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
    var _mapPoints = []; // Array to store clicked points
    var routingControl;
  }else{
    _directionsRenderer = new google.maps.DirectionsRenderer();    
    var directionsService = new google.maps.DirectionsService();
    var myOptions = {    
        zoom: 12 ,    
        center: new google.maps.LatLng(21.7679, 78.8718),    
        mapTypeId: google.maps.MapTypeId.ROADMAP    
    };    
    map = new google.maps.Map(document.getElementById("map"), myOptions);    
    _directionsRenderer.setMap(map);    
  }   

}

  function getRoutePointsAndWaypoints() {    

    if (mapType == "OFFLINE"){
      var waypoints = [];
      if (_mapPoints.length > 2) {
          // Add all points except the first and last one as waypoints
          for (var j = 1; j < _mapPoints.length - 1; j++) {
              var address = _mapPoints[j];    
              if (address !== "") {    
                  waypoints.push(L.latLng(address.lat, address.lng));   
              }    
          }
          drawRoute(_mapPoints[0], _mapPoints[_mapPoints.length - 1], waypoints);
      } else if (_mapPoints.length > 1) {
          drawRoute(_mapPoints[_mapPoints.length - 2], _mapPoints[_mapPoints.length - 1], waypoints);
      } else {
          drawRoute(_mapPoints[_mapPoints.length - 1], _mapPoints[_mapPoints.length - 1], waypoints);
      }
    }else{    
      var _waypoints = new Array();    
      if (_mapPoints.length > 2)
      {    
          for (var j = 1; j < _mapPoints.length - 1; j++) {    
              var address = _mapPoints[j];    
              if (address !== "") {    
                  _waypoints.push({    
                      location: address,    
                      stopover: true  
                  });    
              }    
          }   
          drawRoute(_mapPoints[0], _mapPoints[_mapPoints.length - 1], _waypoints);    
      } else if (_mapPoints.length > 1) {    
          drawRoute(_mapPoints[_mapPoints.length - 2], _mapPoints[_mapPoints.length - 1], _waypoints);    
      } else {    
          drawRoute(_mapPoints[_mapPoints.length - 1], _mapPoints[_mapPoints.length - 1], _waypoints);    
      }    
    }
}

async function drawRoute(originAddress,sosAddress,destinationAddress) {  

    var originLatLng = originAddress.split(',').map(Number);
    var destinationLatLng = destinationAddress.split(',').map(Number);
    var sosLatLng = sosAddress.split(',').map(Number);
    if (mapType == "OFFLINE"){
      var sourceMarker = L.marker(originLatLng).addTo(map).bindPopup("Source = " + sourceLocationName);
      var destinationMarker = L.marker(destinationLatLng).addTo(map).bindPopup("Destination = " + destinationLocationName);
      var sosIcon=L.icon({
        iconUrl: '{!! asset('/images/sos-button-emoji.png') !!}', // Path to custom SOS marker icon
        iconSize: [32,32], // Icon size
        iconAnchor: [16,32], // Point of the icon that corresponds to the marker's location
        popupAnchor: [0,-32] // Offset of the popup position
      });
      var sosMarker = L.marker(sosLatLng,{icon: sosIcon}).addTo(map).bindPopup("SOS point :" + sosLatLng[0] + " , " + sosLatLng[1]);
      L.Routing.control({
      waypoints: [
          L.latLng(originLatLng[0], originLatLng[1]),
          L.latLng(sosLatLng[0], sosLatLng[1]),
          L.latLng(destinationLatLng[0], destinationLatLng[1]),
      ],
      routeWhileDragging: true,
      router: L.Routing.osrmv1({
          serviceUrl: 'https://router.project-osrm.org/route/v1'
      }),
      createMarker: function(i,waypoint,n) {
          var markerOptions={
            draggable: true, // Allow markers to be draggable
          };
          if(i===0) {
            // First marker (A)
            markerOptions.icon=L.divIcon({
              className: 'custom-div-icon',
              html: '<div style="background-color:#2E86C1;color:#fff;border-radius:50%;width:24px;height:24px;display:flex;align-items:center;justify-content:center;">A</div>',
              iconSize: [24,24],
              popupAnchor: [0,-12]
            });
          } else if(i===n-1) {
            // Last marker (B)
            markerOptions.icon=L.divIcon({
              className: 'custom-div-icon',
              html: '<div style="background-color:#E74C3C;color:#fff;border-radius:50%;width:24px;height:24px;display:flex;align-items:center;justify-content:center;">B</div>',
              iconSize: [24,24],
              popupAnchor: [0,-12]
            });
          } else {
            // Middle marker (SOS Point)
            markerOptions.icon=sosIcon;
          }
          return L.marker(waypoint.latLng,markerOptions).bindPopup(i===0? "Source":i===n-1? "Destination":"SOS Point");
        }
  }).addTo(map);
      }else{
        var sourceMarker = new google.maps.Marker({
            position:  { lat: originLatLng[0], lng: originLatLng[1] },
            map: map,
            title: "Source"
          });
          var destinationMarker = new google.maps.Marker({
            position: { lat: destinationLatLng[0], lng: destinationLatLng[1] },
            map: map,
            title: "Destination"
          });
          var sosIcon = {
          url: '{!! asset('/images/sos-button-emoji.png') !!}',
          scaledSize: new google.maps.Size(50, 50), // Adjust size if necessary
        };
          var sosMarker = new google.maps.Marker({
            position: { lat: sosLatLng[0], lng: sosLatLng[1] },
            map: map,
            icon: sosIcon,
            title: "SOS Point"
          });
            var sourceInfoWindow = new google.maps.InfoWindow({
            content: "Source: " + sourceLocationName
          });
          var destinationInfoWindow = new google.maps.InfoWindow({
            content: "Destination: " + destinationLocationName
          });
          var sosInfoWindow = new google.maps.InfoWindow({
            content: "SOS Point: " + sosLatLng[0] + ' , ' + sosLatLng[1]
          });
          sourceMarker.addListener("click", function() {
            sourceInfoWindow.open(map, sourceMarker);
          });
          destinationMarker.addListener("click", function() {
            destinationInfoWindow.open(map, destinationMarker);
          });
          sosMarker.addListener("click", function() {
            sosInfoWindow.open(map, sosMarker);
          });
        var directionsService = new google.maps.DirectionsService();    
          var _request = '';    
          _request = {    
                  origin: originAddress,    
                  destination: destinationAddress,  
                    waypoints: [
                  {
                    location: sosAddress, // SOS point as a waypoint
                    stopover: true // Set to true if you want to stop at the SOS point
                  }
                ],
                  travelMode: google.maps.DirectionsTravelMode.DRIVING    
          }    
          directionsService.route(_request, function (_response, _status) {    
              if (_status == google.maps.DirectionsStatus.OK) {
                  _directionsRenderer.setDirections(_response);    
              }    
          });    
      }  
  }

</script>

@endsection
@extends('layouts.app')
@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.bulk_import_products_plural')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.bulk_import_products_plural')}}</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="admin-top-section">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex top-title-section pb-4 justify-content-between">
                        <div class="d-flex top-title-left align-self-center">
                            <span class="icon mr-3"><img src="{{ asset('images/item_image.png') }}"></span>
                            <h3 class="mb-0">{{trans('lang.item_table')}}</h3>
                            <span class="counter ml-3 item_count"></span>
                        </div>
                        <div class="d-flex top-title-right align-self-center">
                            <div class="select-box pl-3">
                                <select class="form-control category_selector">
                                    <option value=""  selected>{{trans("lang.category_plural")}}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="table-list">
            <div class="row">
                <div class="col-12">
                    <div class="card border">
                        <div class="card-header d-flex justify-content-between align-items-center border-0">
                            <div class="card-header-title">
                                <h3 class="text-dark-2 mb-2 h4">{{trans('lang.item_table')}}</h3>
                                <p class="mb-0 text-dark-2">{{trans('lang.item_table_text')}}</p>
                            </div>
                            <div class="card-header-right d-flex align-items-center">
                               
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive m-t-10">
                                <input type="file" id="importFile" onchange="handleImport(event)" accept=".csv, .xlsx" style="display:none" />
                                <table id="itemTable"
                                    class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                    cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <?php if (in_array('bulk_import_products.delete', json_decode(@session('user_permissions'), true))) { ?>
                                                <th class="delete-all"><input type="checkbox" id="select-all">
                                                    <label class="col-3 control-label" for="select-all">
                                                        <a id="deleteAll" class="do_not_delete" href="javascript:void(0)"><i
                                                                class="mdi mdi-delete"></i> {{trans('lang.all')}}</a>
                                                    </label>
                                                </th>
                                            <?php } ?>
                                            <th>{{trans('lang.item_name')}}</th>
                                            <th>{{trans('lang.item_price')}}</th>
                                            <th>{{trans('lang.item_category_id')}}</th>
                                            <th>{{trans('lang.item_publish')}}</th>
                                            <th>{{trans('lang.actions')}}</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                            <div class="tip-box search-info">
                                <p>{{trans('lang.note')}} {{trans('lang.note_description')}}</p>
                                <h5><i class="fa fa-info-circle"></i> {{ trans('lang.import_instructions_title') }}</h5>
                                <ul>
                                    <li><strong>Step 1</strong>: {{ trans('lang.import_steps.step_1') }}</li>
                                    <li><strong>Step 2</strong>: {{ trans('lang.import_steps.step_2') }}</li>
                                </ul>
                                <p>{!! trans('lang.gallery_images', ['field' => 'Product Images']) !!}</p>
                                <pre>{{ trans('lang.gallery_images_example') }}</pre>
                                <ul>
                                    <li>{{ trans('lang.gallery_images_note') }}</li>
                                </ul>
                                <p>{!! trans('lang.addons.description', ['field' => 'Addons Titles & Prices']) !!}</p>
                                <ul>
                                    <li><strong>Addons Titles</strong>: {!! trans('lang.addons.titles') !!}</li>
                                    <li><strong>Addons Prices</strong>: {!! trans('lang.addons.prices') !!}</li>
                                </ul>
                                <pre>{!! trans('lang.addons.example') !!}</pre>
                                <p>{!! trans('lang.specification.description', ['field' => 'Specification']) !!}</p>
                                <pre>{{ trans('lang.specification.example') }}</pre>
                                <p>{!! trans('lang.attributes.description', ['field' => 'Attributes']) !!}</p>
                                <ul>
                                    <li><strong>Attribute Name</strong>: {!! trans('lang.attributes.name') !!}</li>
                                    <li><strong>Attribute Options</strong>: {!! trans('lang.attributes.options') !!}</li>
                                    <li><strong>Attribute Price & Quantity</strong>: {!! trans('lang.attributes.price_qty') !!}</li>
                                </ul>
                                <pre>{!! trans('lang.attributes.example') !!}</pre>
                                <div>
                                    <p>{{ trans('lang.attributes.available') }}</p>
                                    <div id="available_attributes" style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 5px;"></div>
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
@section('scripts')

<script type="text/javascript">
    
    var section_id = getCookie('section_id') || null;
    
    var database=firebase.firestore();
    var currentCurrency='';
    var currencyAtRight=false;
    var decimal_degits=0;
    var storage=firebase.storage();
    var storageRef=firebase.storage().ref('images');
    var user_permissions='<?php echo @session("user_permissions") ?>';
    user_permissions=Object.values(JSON.parse(user_permissions));
    
    var checkDeletePermission=false;
    if($.inArray('bulk_import_products.delete',user_permissions)>=0) {
        checkDeletePermission=true;
    }
    
    var ref = database.collection('admin_products').where('sectionId','==',section_id);
    
    var refCurrency=regionCurrencyRef();
    var append_list='';
    refCurrency.get().then(async function(snapshots) {
        var currencyData=snapshots.docs[0].data();
        currentCurrency=currencyData.symbol;
        currencyAtRight=currencyData.symbolAtRight;
        if(currencyData.decimal_degits) {
            decimal_degits=currencyData.decimal_degits;
        }
    });

    var placeholderImage='';
    var placeholder=database.collection('settings').doc('placeHolderImage');
    placeholder.get().then(async function(snapshotsimage) {
        var placeholderImageData=snapshotsimage.data();
        placeholderImage=placeholderImageData.image;
    })

    database.collection('vendor_categories').where('section_id','==',section_id).get().then(async function(snapshots) {
        snapshots.docs.forEach((listval) => {
            var data=listval.data();
            $('.category_selector').append($("<option></option>")
                .attr("value",data.id)
                .text(data.title));
        })
    });
    
    database.collection('vendor_attributes').orderBy('title', 'asc').get().then(async function(snapshots) {
        snapshots.docs.forEach((listval) => {
            var data = listval.data();
            
        });
    });

    var $attrDiv = $('#available_attributes');
    $attrDiv.empty();

    database.collection('vendor_attributes').orderBy('title', 'asc').get().then(function(snapshots) {
        snapshots.docs.forEach((doc) => {
            var data = doc.data();
            var title = data.title;

            var attrItem = $('<div></div>').css({
                'display': 'flex',
                'align-items': 'center',
                'gap': '5px',
                'padding': '4px 8px',
                'border': '1px solid #ddd',
                'border-radius': '4px',
                'background': '#f8f8f8'
            });
            var attrText = $('<span></span>').text(title);
            var copyBtn = $('<button type="button">Copy</button>').css({
                'padding': '2px 6px',
                'font-size': '12px',
                'cursor': 'pointer'
            });
            copyBtn.on('click', function() {
                navigator.clipboard.writeText(title).then(function() {
                    alert('Copied: ' + title);
                });
            });
            attrItem.append(attrText).append(copyBtn);
            $attrDiv.append(attrItem);
        });
    });
    
    var initialRef=ref;

    $('select').change(async function() {
        var category = $('.category_selector').val();
        refData = initialRef;
        if (category) {
            refData=refData.where('categoryID','==',category);
        }
         ref=refData;
        $('#itemTable').DataTable().ajax.reload();
    });

    $(document).ready(function() {
        $('.item_type_selector').select2({
            placeholder: "{{trans('lang.type')}}",  
            minimumResultsForSearch: Infinity,
            allowClear: true  
        });
        $('.category_selector').select2({
            placeholder: "{{trans('lang.category')}}",  
            minimumResultsForSearch: Infinity,
            allowClear: true  
        });
        $('select').on("select2:unselecting", function(e) {
            var self = $(this);
            setTimeout(function() {
                self.select2('close');
            }, 0);
        });
        $('#category_search_dropdown').hide();
        $(document.body).on('click','.redirecttopage',function() {
            var url=$(this).attr('data-url');
            window.location.href=url;
        });
        jQuery("#data-table_processing").show();
        $(document).on('click', '.dt-button-collection .dt-button', function () {
            $('.dt-button-collection').hide();
            $('.dt-button-background').hide();
        });
        $(document).on('click', function (event) {
            if (!$(event.target).closest('.dt-button-collection, .dt-buttons').length) {
                $('.dt-button-collection').hide();
                $('.dt-button-background').hide();
            }
        });
        var fieldConfig = {
            columns: [
                { key: 'itemName', header: "{{trans('lang.item_name')}}" },
                { key: 'category', header: "{{trans('lang.category')}}" },
                { key: 'finalPrice', header: "{{trans('lang.item_price')}}" },
            ],
            fileName: "{{trans('lang.item_table')}}",
        };
        const table=$('#itemTable').DataTable({
            pageLength: 10, // Number of rows per page
            processing: false, // Show processing indicator
            serverSide: true, // Enable server-side processing
            responsive: true,
            ajax: async function(data,callback,settings) {
                const start=data.start;
                const length=data.length;
                const searchValue=data.search.value.toLowerCase();
                const orderColumnIndex=data.order[0].column;
                const orderDirection=data.order[0].dir;
                const orderableColumns=(checkDeletePermission)? ['','itemName','finalPrice','category','','']:['name','finalPrice','category','',''];
                const orderByField=orderableColumns[orderColumnIndex]; // Adjust the index to match your table
                if(searchValue.length>=3||searchValue.length===0) {
                    $('#data-table_processing').show();
                }
                await ref.get().then(async function(querySnapshot) {
                    if(querySnapshot.empty) {
                        $('.item_count').text(0);
                        console.error("No data found in Firestore.");
                        $('#data-table_processing').hide(); // Hide loader
                        callback({
                            draw: data.draw,
                            recordsTotal: 0,
                            recordsFiltered: 0,
                            filteredData: [],
                            data: [] // No data
                        });
                        return;
                    }
                    
                    var categoryNames={};
                    const categoryDocs=await database.collection('vendor_categories').get();
                    categoryDocs.forEach(doc => {
                        categoryNames[doc.id]=doc.data().title;
                    });
                    let records=[];
                    let filteredRecords=[];
                    await Promise.all(querySnapshot.docs.map(async (doc) => {
                        let childData=doc.data();
                        childData.id=doc.id; // Ensure the document ID is included in the data
                        var finalPrice=0;
                        if(childData.hasOwnProperty('disPrice')&&childData.disPrice!=''&&childData.disPrice!='0') {
                            childData.finalPrice=childData.disPrice;
                        } else {
                            childData.finalPrice=childData.price;
                        }
                        
                        childData.itemName=childData.name;
                        childData.finalPrice=parseInt(finalPrice);
                        childData.category=categoryNames[childData.categoryID]||'';
                        if(searchValue) {
                            if(
                                (childData.name&&childData.name.toString().toLowerCase().includes(searchValue))||
                                (childData.finalPrice&&childData.finalPrice.toString().includes(searchValue))||
                                (childData.category&&childData.category.toString().toLowerCase().includes(searchValue))
                            ) {
                                filteredRecords.push(childData);
                            }
                        } else {
                            filteredRecords.push(childData);
                        }
                    }));
                    filteredRecords.sort((a,b) => {
                        let aValue=a[orderByField];
                        let bValue=b[orderByField];
                        if(orderByField==='finalPrice') {
                            aValue=a[orderByField]? parseInt(a[orderByField]):0;
                            bValue=b[orderByField]? parseInt(b[orderByField]):0;
                        } else {
                            aValue=a[orderByField]? a[orderByField].toString().toLowerCase():'';
                            bValue=b[orderByField]? b[orderByField].toString().toLowerCase():''
                        }
                        if(orderDirection==='asc') {
                            return (aValue>bValue)? 1:-1;
                        } else {
                            return (aValue<bValue)? 1:-1;
                        }
                    });
                    const totalRecords=filteredRecords.length;
                    $('.item_count').text(totalRecords);
                    const paginatedRecords=filteredRecords.slice(start,start+length);
                    await Promise.all(paginatedRecords.map(async (childData) => {
                        var getData=await buildHTML(childData);
                        records.push(getData);
                    }));
                    $('#data-table_processing').hide(); // Hide loader
                    callback({
                        draw: data.draw,
                        recordsTotal: totalRecords, // Total number of records in Firestore
                        recordsFiltered: totalRecords, 
                        filteredData: filteredRecords,
                        data: records // The actual data to display in the table
                    });
                }).catch(function(error) {
                    console.error("Error fetching data from Firestore:",error);
                    $('#data-table_processing').hide(); // Hide loader
                    callback({
                        draw: data.draw,
                        recordsTotal: 0,
                        recordsFiltered: 0,
                        filteredData: [], 
                        data: [] // No data due to error
                    });
                });
            },
            order: (checkDeletePermission)? [1,'asc']:[0,'asc'],
            columnDefs: [
                {
                    orderable: false,
                    targets: (checkDeletePermission)? [0,4,5]:[3,4]
                },
                {
                    type: 'formatted-num',
                    targets: (checkDeletePermission)? [2]:[3]
                }
            ],
            "language": datatableLang,
            dom: 'lfrtipB',
            buttons: [
                {
                    extend: 'collection',
                    text: '<i class="mdi mdi-cloud-download"></i> {{trans("lang.export_as")}}',
                    className: 'btn btn-info',
                    buttons: [
                        {
                            extend: 'excelHtml5',
                            text: '{{trans("lang.export_excel")}}',
                            action: function (e, dt, button, config) {
                                exportData(dt, 'excel',fieldConfig);
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            text: '{{trans("lang.export_pdf")}}',
                            action: function (e, dt, button, config) {
                                exportData(dt, 'pdf',fieldConfig);
                            }
                        },   
                        {
                            extend: 'csvHtml5',
                            text: '{{trans("lang.export_csv")}}',
                            action: function (e, dt, button, config) {
                                exportData(dt, 'csv',fieldConfig);
                            }
                        }
                    ]
                },
                {
                    extend: 'collection',
                    text: '<i class="mdi mdi-upload"></i> {{trans("lang.import")}}',
                    className: 'btn btn-success',
                    buttons: [{
                            text: '{{trans("lang.upload_file")}}',
                            action: function() {
                                $('#importFile').click();
                            }
                        },
                        {
                            text: '{{trans("lang.sample_file")}}',
                            action: function() {
                                const link = document.createElement('a');
                                link.href = '/products_sample_file.csv';
                                link.download = 'Products Sample File.csv';
                                document.body.appendChild(link);
                                link.click();
                                document.body.removeChild(link);
                            }
                        }
                    ]
                }
            ],
            initComplete: function() {
                $(".dataTables_filter").append($(".dt-buttons").detach());
                $('.dataTables_filter input').attr('placeholder', '{{trans("lang.search_here")}}').attr('autocomplete','new-password').val('');
                $('.dataTables_filter label').contents().filter(function() {
                    return this.nodeType === 3; 
                }).remove();
            }
        });
        $('#search-input').on('input',debounce(function() {
            const searchValue=$(this).val();
            if(searchValue.length>=3) {
                $('#data-table_processing').show();
                table.search(searchValue).draw();
            } else if(searchValue.length===0) {
                $('#data-table_processing').show();
                table.search('').draw();
            }
        },300));
    });

    function debounce(func,wait) {
        let timeout;
        const context=this;
        return function(...args) {
            clearTimeout(timeout);
            timeout=setTimeout(() => func.apply(context,args),wait);
        };
    }

    async function buildHTML(val) {
        var html=[];
        newdate='';
        var imageHtml='';
        var id=val.id;
        var route1='{{route("bulk_import_products.edit", ":id")}}';
        route1=route1.replace(':id',id);
        
        if(val.photos!=''&&val.photos!=null) {
           imageHtml='<img onerror="this.onerror=null;this.src=\''+placeholderImage+'\'" class="rounded" width="100%" style="width:70px;height:70px;" src="'+val.photo+'" alt="image">';
        } else if(val.photo!=''&&val.photos!=null) {
           imageHtml='<img onerror="this.onerror=null;this.src=\''+placeholderImage+'\'" class="rounded" width="100%" style="width:70px;height:70px;" src="'+val.photo+'" alt="image">';
        } else {
            imageHtml='<img width="100%" style="width:70px;height:70px;" src="'+placeholderImage+'" alt="image">';
        }
        if(checkDeletePermission) {
            html.push('<td class="delete-all"><input type="checkbox" id="is_open_'+id+'" name="record" class="is_open" dataId="'+id+'"><label class="col-3 control-label"\n'+
                'for="is_open_'+id+'" ></label></td>');
        }
        
        html.push(imageHtml+'<a href="'+route1+'" >'+val.name+'</a>');
        if (val.item_attribute && val.item_attribute.variants && val.item_attribute.variants.length > 0) {
            
             let originalVariantPrices = val.item_attribute.variants
                    .map(v => parseFloat(v.variant_price))
                    .filter(price => !isNaN(price) && price > 0);

                if (originalVariantPrices.length > 0) {

                    const displayMin = Math.min(...originalVariantPrices);
                    const displayMax = Math.max(...originalVariantPrices);

                    let minPriceFormatted = '';
                    let maxPriceFormatted = '';

                    if (currencyAtRight) {
                        minPriceFormatted = parseFloat(displayMin).toFixed(decimal_degits) + '' + currentCurrency;
                        maxPriceFormatted = parseFloat(displayMax).toFixed(decimal_degits) + '' + currentCurrency;
                    } else {
                        minPriceFormatted = currentCurrency + '' + parseFloat(displayMin).toFixed(decimal_degits);
                        maxPriceFormatted = currentCurrency + '' + parseFloat(displayMax).toFixed(decimal_degits);
                    }

                    if (displayMin === displayMax) {
                        html.push(minPriceFormatted);
                    } else {
                        html.push(minPriceFormatted + ' - ' + maxPriceFormatted);
                    }
                }
        }
        else if(val.hasOwnProperty('disPrice')&&val.disPrice!=''&&val.disPrice!='0') {
            if(currencyAtRight) {
                html.push('<span class="text-green">'+parseFloat(val.disPrice).toFixed(decimal_degits)+''+currentCurrency+'  <s>'+parseFloat(val.price).toFixed(decimal_degits)+''+currentCurrency+'</s>');
            } else {
                html.push('<span class="text-green">'+''+currentCurrency+parseFloat(val.disPrice).toFixed(decimal_degits)+'  <s>'+currentCurrency+''+parseFloat(val.price).toFixed(decimal_degits)+'</s>');
            }
        } else {
            if(currencyAtRight) {
                html.push('<span class="text-green">'+parseFloat(val.price).toFixed(decimal_degits)+''+currentCurrency);
            } else {
                html.push('<span class="text-green">'+currentCurrency+''+parseFloat(val.price).toFixed(decimal_degits));
            }
        }
        
        var caregoryroute='{{route("categories.edit", ":id")}}';
        caregoryroute=caregoryroute.replace(':id',val.categoryID);
        html.push('<a href="'+caregoryroute+'">'+val.category+'</a>');
        if(val.publish) {
            html.push('<label class="switch"><input type="checkbox" checked id="'+val.id+'" name="isActive"><span class="slider round"></span></label>');
        } else {
            html.push('<label class="switch"><input type="checkbox" id="'+val.id+'" name="isActive"><span class="slider round"></span></label>');
        }
        var actionHtml='';
        actionHtml+='<span class="action-btn"><a href="'+route1+'" class="link-td"><i class="mdi mdi-lead-pencil" title="Edit"></i></a>';
        if(checkDeletePermission) {
            actionHtml+='<a id="'+val.id+'" name="item-delete" href="javascript:void(0)" class="delete-btn"><i class="mdi mdi-delete"></i></a>';
        }
        actionHtml+='</span>';
        html.push(actionHtml);
        return html;
    }

    async function checkIfImageExists(url,callback) {
        const img=new Image();
        img.src=url;
        if(img.complete) {
            callback(true);
        } else {
            img.onload=() => {
                callback(true);
            };
            img.onerror=() => {
                callback(false);
            };
        }
    }

    $(document).on("click","input[name='isActive']",function(e) {
        var ischeck=$(this).is(':checked');
        var id=this.id;
        if(ischeck) {
            database.collection('admin_products').doc(id).update({
                'publish': true
            }).then(function(result) {
            });
        } else {
            database.collection('admin_products').doc(id).update({
                'publish': false
            }).then(function(result) {
            });
        }
    });

    $(document).on("click","a[name='item-delete']",async function(e) {
        var id=this.id;
        await deleteDocumentWithImage('admin_products',id,'photo','photos');
        window.location.reload();
    });

    $(document.body).on('change','#selected_search',function() {
        if(jQuery(this).val()=='category') {
            var ref_category=database.collection('vendor_categories');
            ref_category.get().then(async function(snapshots) {
                snapshots.docs.forEach((listval) => {
                    var data=listval.data();
                    $('#category_search_dropdown').append($("<option></option").attr("value",data.id).text(data.title));
                });
            });
            jQuery('#search').hide();
            jQuery('#category_search_dropdown').show();
        } else {
            jQuery('#search').show();
            jQuery('#category_search_dropdown').hide();
        }
    });

    $('#select-all').change(function() {
        var isChecked=$(this).prop('checked');
        $('input[type="checkbox"][name="record"]').prop('checked',isChecked);
    });
    
    $('#deleteAll').click(function() {
        if(confirm("{{trans('lang.selected_delete_alert')}}")) {
            jQuery("#data-table_processing").show();
            // Loop through all selected records and delete them
            $('input[type="checkbox"][name="record"]:checked').each(async function() {
                var id=$(this).attr('dataId');
                await deleteDocumentWithImage('admin_products',id,'photo','photos');
                window.location.reload();
            });
        }
    });

    function handleImport() {
        const file = event.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function(e) {
            const data = new Uint8Array(e.target.result);
            const workbook = XLSX.read(data, {
                type: 'array'
            });

            const firstSheetName = workbook.SheetNames[0];
            const worksheet = workbook.Sheets[firstSheetName];
            const jsonData = XLSX.utils.sheet_to_json(worksheet, {
                header: 1
            });

            if (jsonData.length < 2) {
                console.error("No data to import.");
                return;
            }

            const headers = jsonData[0]; // First row is header
            const dataRows = jsonData.slice(1);

            const headerMapping = {
                "Section Name": "sectionName",
                "Name": "name",
                "Description": "description",
                "Price": "price",
                "Discounted Price": "disPrice",
                "Quantity": "quantity",
                "Category": "category",
                "Product Images": "photos",
                "Calories": "calories",
                "Grams": "grams",
                "Proteins": "proteins",
                "Fats": "fats",
                "Veg": "veg",
                "Addons Titles": "addOnsTitle",
                "Addons Prices": "addOnsPrice",
                "Takeaway Option": "takeawayOption",
                "Specification": "product_specification",
                "Attribute Name": "attribute_name",
                "Attribute Options": "attribute_options",
                "Attribute Price": "attribute_price",
                "Attribute Quantity": "attribute_quantity",
            };

            const records = [];
            for (const row of dataRows) {
                let record = {};
                headers.forEach((header, index) => {
                    const firebaseField = headerMapping[header];
                    if (firebaseField) {
                        record[firebaseField] = row[index] ?? null;
                    }
                });
                records.push(record);
            }

            showProcessing();

            importProductsInBatches(records).then(({
                successCount,
                failureCount
            }) => {
                if (failureCount === 0) {
                    showSuccessMessage(`${successCount} {{ trans('lang.products_imported_successfully') }}`);
                    setTimeout(() => {
                        window.location.href = '{{ url()->current() }}';
                    }, 2500);
                } else {
                    showErrorMessage(`${successCount} {{ trans('lang.products_imported') }}, ${failureCount} {{ trans('lang.import_failed_message') }}`);
                }
            });
        };

        reader.readAsArrayBuffer(file);
    }
    
    async function importProductsInBatches(records) {
        const BATCH_SIZE = 500;
        let successCount = 0;
        let failureCount = 0;

        for (let i = 0; i < records.length; i += BATCH_SIZE) {
            const batch = database.batch();
            const chunk = records.slice(i, i + BATCH_SIZE);
            
            for (const record of chunk) {
                try {
                    
                    let photos = record.photos ? record.photos.split(',').map(url => url.trim()) : [];

                    record.id = database.collection('temp').doc().id;
                    record.name = record.name;
                    record.description = record.description;
                    record.price = record.price != null ? record.price.toString() : "0";
                    record.disPrice = record.disPrice != null ? record.disPrice.toString() : "0";
                    record.quantity = record.quantity != null ? Number(record.quantity) : 0;
                    record.sectionId = await getSectionId(record.sectionName);
                    record.categoryID = await getCatgoryId(record.category);
                    record.photo = photos?.length ? photos[0] : null;
                    record.photos = photos;
                    record.calories = Number(record.calories) || 0;
                    record.grams = Number(record.grams) || 0;
                    record.proteins = Number(record.proteins) || 0;
                    record.fats = Number(record.fats) || 0;
                    record.veg = record.veg == "Yes" ? true : false;
                    record.nonveg = record.veg == true ? false : true;
                    record.takeawayOption = record.takeawayOption == "Yes" ? true : false;
                    record.createdAt = firebase.firestore.FieldValue.serverTimestamp();
                    record.publish = true;

                    if (record.addOnsTitle) {
                        const titleStr = record.addOnsTitle.toString();
                        record.addOnsTitle = titleStr.includes('|') 
                            ? titleStr.split('|').map(v => v.trim())
                            : [titleStr.trim()];
                    } else {
                        record.addOnsTitle = [];
                    }

                    if (record.addOnsPrice) {
                        const priceStr = record.addOnsPrice.toString();
                        record.addOnsPrice = priceStr.includes('|') 
                            ? priceStr.split('|').map(v => v.trim())
                            : [priceStr.trim()];
                    } else {
                        record.addOnsPrice = [];
                    }

                    if (record.attribute_name && record.attribute_options) {
                        record.item_attribute = await buildItemAttribute(
                            record.attribute_name,
                            record.attribute_options,
                            record.attribute_price,
                            record.attribute_quantity
                        );
                    }else{
                        record.item_attribute = null;
                    }

                    let product_specification = {};
                    if (record.product_specification){
                        product_specification = record.product_specification.split('|').reduce((acc, item) => {
                            const [key, value] = item.split(':');
                            if (key && value) acc[key.trim()] = value.trim();
                            return acc;
                        }, {});
                    } {};
                    record.product_specification = product_specification;
                   
                    delete record.attribute_name;
                    delete record.attribute_options;
                    delete record.attribute_price;
                    delete record.attribute_quantity;
                    delete record.sectionName;

                    let docRef = database.collection('admin_products').doc(record.id);
                    batch.set(docRef, record);
                    
                    successCount++;
                } catch (err) {
                    console.error("Failed to process a record:", record.name, err);
                    failureCount++;
                }
            }

            await batch.commit();
        }

        return {
            successCount,
            failureCount
        };
    }

    async function getAttributeIdByName(attributeName) {
        const snapshot = await database.collection('vendor_attributes').where('title', '==', attributeName).get();
        if (snapshot.empty) {
            return '';
        }
        return snapshot.docs[0].id;
    }

    function getCombinations(arr) {
        if (arr.length) {
            if (arr.length == 1) {
                return arr[0];
            } else {
                var result = [];
                var allCasesOfRest = getCombinations(arr.slice(1));
                for (var i = 0; i < allCasesOfRest.length; i++) {
                    for (var j = 0; j < arr[0].length; j++) {
                        result.push(arr[0][j] + '-' + allCasesOfRest[i]);
                    }
                }
                return result;
            }
        }
    }
    
    function buildVariantsWithPriceQty(attributeOptions, attributePrice, attributeQuantity) {
        const combinations = getCombinations(attributeOptions);
        return combinations.map(combo => {
            const variantPrice = attributePrice ?? 0;
            const variantQty = attributeQuantity ?? "-1";
            return {
                variant_id: Math.random().toString(36).substr(2, 14),
                variant_sku: combo,
                variant_price: variantPrice.toString(),
                variant_quantity: variantQty.toString()
            };
        });
    }

    async function buildItemAttribute(attrNamesStr, attrOptionsStr, attrPrice, attrQty) {
        const names = attrNamesStr.split('|');
        const options = attrOptionsStr.split('|').map(o => o.split(','));
        let attributes = [];
        for (let i = 0; i < names.length; i++) {
            const attributeId = await getAttributeIdByName(names[i]);
            attributes.push({
                attribute_id: attributeId,
                attribute_options: options[i]
            });
        }
        const variants = buildVariantsWithPriceQty(options, attrPrice, attrQty);
        return {
            attributes,
            variants
        };
    }

    async function getCatgoryId(category) {
        let categorySnapshot = await database.collection('vendor_categories').where('title', '==', category).limit(1).get();
        if (!categorySnapshot.empty) {
            const doc = categorySnapshot.docs[0];
            return doc.id;
        }
        return "";
    }

    async function getSectionId(sectionName) {
        let sectionSnapshot = await database.collection('sections').where('name', '==', sectionName).limit(1).get();
        if (!sectionSnapshot.empty) {
            const doc = sectionSnapshot.docs[0];
            return doc.id;
        }
        return "";
    }
    
    
</script>
@endsection
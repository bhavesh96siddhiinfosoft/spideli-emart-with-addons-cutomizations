@extends('layouts.app')

@section('content')

<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.role_plural')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.role_table')}}</li>
            </ol>
        </div>
        <div>
        </div>
    </div>
    <div class="container-fluid">
       <div class="admin-top-section"> 
        <div class="row">
            <div class="col-12">
                <div class="d-flex top-title-section pb-4 justify-content-between">
                    <div class="d-flex top-title-left align-self-center">
                        <span class="icon mr-3"><img src="{{ asset('images/role.png') }}"></span>
                        <h3 class="mb-0">{{trans('lang.role_plural')}}</h3>
                        <span class="counter ml-3 total_count">{{ $roles->count() }}</span>
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
                    <h3 class="text-dark-2 mb-2 h4">{{trans('lang.role_table')}}</h3>
                    <p class="mb-0 text-dark-2">{{trans('lang.role_table_text')}}</p>
                   </div>
                   <div class="card-header-right d-flex align-items-center">
                    <div class="card-header-btn mr-3"> 

                        <a class="btn-primary btn rounded-full" href="{!! route('role.save') !!}"><i class="mdi mdi-plus mr-2"></i>{{trans('lang.create_role')}}</a>
                     </div>
                   </div>                
                 </div>
                 <div class="card-body">
                         <div class="table-responsive m-t-10">
                            <table id="roleTable"
                                   class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                   cellspacing="0" width="100%">
                                <thead>
                                <tr>
                                    <?php if (in_array('role.delete', json_decode(@session('user_permissions'),true))) { ?>
                                    <th class="delete-all"><input type="checkbox" id="is_active"><label class="col-3 control-label" for="is_active"><a id="deleteAll"
                                    class="do_not_delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i> {{trans('lang.all')}}</a></label></th>
                                    <?php } ?>
                                    <th>{{trans('lang.name')}}</th>
                                    <th>{{trans('lang.actions')}}</th>
                                </tr>
                                </thead>  
                                <tbody id="append_list1">                                    
                        
                                    @foreach($roles as $role)
                                        <tr>
                                            <?php if (in_array('role.delete', json_decode(@session('user_permissions'),true))) { ?>                                             
                                                <td class="delete-all">
                                                    @if($role->role_name!="Super Administrator")
                                                    <input type="checkbox" id="is_open_{{$role->id}}" class="is_open" dataid="{{$role->id}}">
                                                    <label class="col-3 control-label" for="is_open_{{$role->id}}"></label>
                                                    @endif
                                                </td>
                                            <?php }?>
                                            <td>
                                                <a href="{{route('role.edit', ['id' => $role->id])}}">{{ $role->role_name}}</a>
                                            </td>
                                            <td><span class="action-btn">
                                                <a href="{{route('role.edit', ['id' => $role->id])}}" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.edit') }}"><i class="mdi mdi-lead-pencil"></i></a>
                                                @if($role->role_name!="Super Administrator")
                                                    @if(in_array('role.delete', json_decode(@session('user_permissions'),true)))
                                                        <a href="{{route('role.delete', ['id' => $role->id])}}" class="delete-btn" data-toggle="tooltip" data-bs-original-title="{{ trans('lang.delete') }}"><i
                                                                class="mdi mdi-delete"></i></a>
                                                    @endif
                                                @endif            
                                            </span></td>
                                        </tr>
                                    @endforeach
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

@endsection

@section('scripts')

<script type="text/javascript">

    var user_permissions = '<?php echo @session('user_permissions') ?>';
    user_permissions = Object.values(JSON.parse(user_permissions));
    var checkDeletePermission = false;

    if ($.inArray('role.delete', user_permissions) >= 0) {
        checkDeletePermission = true;
    }

    const table = $('#roleTable').DataTable({
        order: [],
        columnDefs: [
            { orderable: false, targets: (checkDeletePermission==true) ? [0,2] : [1] },

        ],
        "language": datatableLang,

        responsive: true
    });
    table.on('search.dt', function() {
        var filteredCount = table.rows({ search: 'applied' }).count();
        $('.total_count').text(filteredCount);  // Update count
    });

    $("#is_active").click(function () {
        $("#roleTable .is_open").prop('checked', $(this).prop('checked'));

    });

    $("#deleteAll").click(function () {
        if ($('#roleTable .is_open:checked').length) {
            if (confirm("{{trans('lang.selected_delete_alert')}}")) {
                var arrayUsers = [];
                $('#roleTable .is_open:checked').each(function () {
                    var dataId = $(this).attr('dataId');
                    arrayUsers.push(dataId);

                });

                arrayUsers = JSON.stringify(arrayUsers);
                var url = "{{url('role/delete', 'id')}}";
                url = url.replace('id', arrayUsers);

                $(this).attr('href', url);
            }
        } else {
            alert("{{trans('lang.select_delete_alert')}}");
        }
    });
</script>


@endsection
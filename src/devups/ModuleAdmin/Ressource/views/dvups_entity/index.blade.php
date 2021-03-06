
@extends('layout')
@section('title', 'List')


@section('cssimport')

                <style></style>
                
@show

@section('content')

        <div class="row">
                <div class="col-lg-12">
                        <ol class="breadcrumb">
                                <li class="active">
                                        <i class="fa fa-dashboard"></i> <?php echo CHEMINMODULE; ?>  > Liste 
                                </li>
                        </ol>
                </div>
                <div class="col-lg-12"> <?= $__navigation  ?></div>
        </div>
        <div class="row">
                
        <div class="col-lg-12 col-md-12">
                
                    <?= \DClass\devups\Datatable::buildtable($lazyloading, [
['header' => 'Name', 'value' => 'name'], 
['header' => 'Dvups_module', 'value' => 'Dvups_module.name'],
['header' => 'Label', 'value' => 'label', 'get' => 'labelform']
])->render(); ?>

        </div>
			
        </div>
        
        <div class="modal fade" id="dvups_entitymodal" tabindex="-1" role="dialog"
             aria-labelledby="modallabel">
            <div  class="modal-dialog" role="document">
                <div class="modal-content">
        
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"
                                aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h3 class="title" id="modallabel">Modal Label</h3>
                    </div>
                    <div class="modal-body panel generalinformation"> </div>
                    <div class="modal-footer">
                        <button data-dismiss="modal" aria-label="Close" type="button" class="btn btn-danger" >Close</button>
                    </div>
        
                </div>
        
            </div>
        </div>
        
@endsection

@section('jsimport')
        <script src="<?= CLASSJS ?>model.js"></script>
        <script src="<?= CLASSJS ?>ddatatable.js"></script>
                <script>
                    var dom = null;
                    function updatelabel(id) {
                        var label = $("#input-"+id).val();
                        dom.find("span").html(label);
                        console.log(id, label);

                        $.get("services.php?path=dvups_entity.updatelabel&id="+id, {
                            label : label
                        }, function (response) {
                            console.log(response);

                            dom.next().hide();
                            dom.show();
                        });
                    }

                    function cancelupdate() {
                        dom.next().show();
                        dom.hide();
                    }

                    function editlabel($this) {
                        dom = $($this).parent();
                        dom.next().show();
                        dom.hide();
                    }
                </script>
@show
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
                    <i class="fa fa-dashboard"></i> <?php echo CHEMINMODULE; ?> > Liste
                </li>
            </ol>
        </div>
        <div class="col-lg-12">
            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-12 ">
                                    <h5>Manage Admin</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="float: right; margin-right: 30px;" class="panel">
                    <?= Genesis::top_action(Dvups_admin::class); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="row">

        <div class="col-lg-12 col-md-12">
            <div class="">

                <?= \DClass\devups\Datatable::buildtable($lazyloading, [
                    ['header' => 'nom', 'value' => 'name'],
                    ['header' => 'login', 'value' => 'login'],
                ])->addcustomaction("callbackbtn")->render();
                ?>


            </div>
        </div>

    </div>

    <div class="modal fade" id="dvups_adminmodal" tabindex="-1" role="dialog"
         aria-labelledby="modallabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"
                            aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h3 class="title" id="modallabel">Modal Label</h3>
                </div>
                <div class="modal-body panel generalinformation"></div>
                <div class="modal-footer">
                    <button data-dismiss="modal" aria-label="Close" type="button" class="btn btn-danger">Close</button>
                </div>

            </div>

        </div>
    </div>
@endsection

@section('jsimport')

    <script src="<?= CLASSJS ?>model.js"></script>
    <script src="<?= CLASSJS ?>ddatatable.js"></script>
    <script></script>
@show
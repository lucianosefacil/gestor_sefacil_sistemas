<div class="modal fade" id="modalSuperadminDelete" tabindex="-1">
    <div class="modal-dialog">
        <form id="formSuperadminDelete">
            @csrf
            <input type="hidden" name="purchase_id">
            <input type="hidden" name="delete_url">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Confirmação de Superadmin</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nome (ou e-mail)</label>
                        <input class="form-control" name="username" required>
                    </div>
                    <div class="form-group">
                        <label>Senha</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <small class="text-muted">Somente usuários listados em <code>ADMINISTRATOR_USERNAMES</code> podem
                        autorizar a exclusão.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                    <button type="submit" class="btn btn-danger">Confirmar exclusão</button>
                </div>
            </div>
        </form>
    </div>
</div>

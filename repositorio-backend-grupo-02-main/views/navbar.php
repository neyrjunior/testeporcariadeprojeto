
<nav class="sidebar-left" aria-label="Navegação principal">

    <!-- LOGO DO UMHELP -->
<a class="logo" href="index.php?action=home">
    <img src="assets/img/logo.png" alt="Logo UmHelp">
</a>


    <!-- MENU DE NAVEGAÇÃO -->
    <ul class="nav-links">

        <!-- INÍCIO -->
        <li>
            <a class="<?= $action === 'home' ? 'active' : '' ?>"
               href="index.php?action=home">
                <span class="nav-icon">⌂</span>
                <span>Início</span>
            </a>
        </li>

        <!-- PESQUISAR -->
        <li>
            <a class="<?= $action === 'pesquisar' ? 'active' : '' ?>"
               href="index.php?action=pesquisar">
                <span class="nav-icon">⌕</span>
                <span>Pesquisar</span>
            </a>
        </li>


        <!-- OPÇÕES PARA USUÁRIO AUTENTICADO -->
        <?php if ($autenticado): ?>

            <!-- NOVO RELATO -->
            <li>
                <a href="index.php?action=home#novo-relato">
                    <span class="nav-icon">＋</span>
                    <span>Novo relato</span>
                </a>
            </li>

            <li>
                <a class="<?= $action === 'perfil' ? 'active' : '' ?>" href="index.php?action=perfil">
                    <span class="nav-icon">♙</span>
                    <span>Meu perfil</span>
                </a>
            </li>

            <li>
                <a class="<?= $action === 'mensagens' ? 'active' : '' ?>" href="index.php?action=mensagens">
                    <span class="nav-icon">✉</span>
                    <span>Mensagens</span>
                </a>
            </li>

            <?php if (($_SESSION['user_profile'] ?? '') === 'root'): ?>
                <li>
                    <a class="<?= $action === 'admin' ? 'active' : '' ?>" href="index.php?action=admin">
                        <span class="nav-icon">⚙</span>
                        <span>Gerenciar relatos</span>
                    </a>
                </li>
            <?php endif; ?>

            <li>
                <form action="index.php" method="post" class="nav-logout">
                    <input type="hidden" name="action" value="sair">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit"><span class="nav-icon">⇥</span><span>Sair</span></button>
                </form>
            </li>


        <!-- OPÇÕES PARA USUÁRIO NÃO AUTENTICADO -->
        <?php else: ?>

            <!-- ENTRAR -->
            <li>
                <a class="<?= $action === 'login' ? 'active' : '' ?>"
                   href="index.php?action=login">
                    <span class="nav-icon">＋</span>
                    <span>Entrar</span>
                </a>
            </li>

            <!-- CRIAR CONTA -->
            <li>
                <a class="<?= $action === 'cadastro' ? 'active' : '' ?>"
                   href="index.php?action=cadastro">
                    <span class="nav-icon">♟</span>
                    <span>Criar conta</span>
                </a>
            </li>

        <?php endif; ?>

    </ul>


    <!-- INFORMAÇÕES DO USUÁRIO AUTENTICADO -->
    <?php if ($autenticado): ?>

        <a class="sidebar-user" href="index.php?action=perfil">
            <span class="user-dot"></span>
            <span>
                <?= htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') ?>
            </span>
        </a>

    <?php endif; ?>

</nav>

<?php


namespace Source\App;


use Source\Core\Connect;
use Source\Core\Controller;
use Source\Models\Category;
use Source\Models\Faq\Channel;
use Source\Models\Faq\Question;
use Source\Models\post;
use Source\Models\User;
use Source\Support\Pager;

/**
 * Class Web
 *  WEB CONTROLLER
 * @package Source\App
 */
class Web extends Controller
{
    /**
     * Web constructor.
     */
    public function __construct()
    {
        Connect::getInstance();
        parent::__construct(__DIR__ . "/../../themes/". CONF_VIEW_THEME. "/");
    }

    /** a função não é retornar dados e sim executar o template e executar a tela para o usuário se comunicando com o modelo*/
    public function home(): void
    {
        //otimização do SEO. É oque vai aparecer nas redes sociais
        $head = $this->seo->render(
            CONF_SITE_NAME . " - " . CONF_SITE_TITLE,
            CONF_SITE_DESC,
            url(),
            theme("/assets/images/share.jpg")
        );

        echo $this->view->render("home", [
            "head" => $head,
            "video" => "UjEaBL1nHAg",
            "blog" => (new Post())->find()
            ->order("post_at DESC")
            ->limit(6)->fetch(true) // o ultimo post publicado primeiro 
        ]);
    }

    /**
     * Site About
     */
    public function about(): void
    {
        $head = $this->seo->render(
            "Descubra o " . CONF_SITE_NAME . " - " . CONF_SITE_DESC,
            CONF_SITE_DESC,
            url("/sobre"),
            theme("/assets/images/share.jpg")
        );

        echo $this->view->render("about", [
            "head" => $head,
            "video" => "UjEaBL1nHAg",
            "faq" => (new Question())
            ->find("channel_id = :id", "id=1", "question, response")
            ->order("order_by")
            ->fetch(true)
        ]);
    }

    /**
     * @param  array|null  $data
     */
    public function blog(?array $data): void
    {
        $head = $this->seo->render(
            "Blog - " . CONF_SITE_NAME,
            "Confira em nosso blog dicas e sacadas de como controlar, melhorar suas contas. Vamos tomar um Café?",
            url("/blog"),
            theme("/assets/images/share.jpg")
        );

        //aonde a paginação de fato vai ser inserida
        $blog = (new Post())->find();
        $pager = new Pager(url("/blog/p/"));
        $pager->pager($blog->count(), 9, $data['page'] ?? 1);

        echo $this->view->render("blog", [
            "head" => $head,
            "blog" => $blog->limit($pager->limit())->offset($pager->offset())->fetch(true),
            "paginator" => $pager->render()
        ]);
    }

    /**
     * SITE BLOG SEARCH
     */
    public function blogSearch(array $data): void
    {
        //"s" indice que vai fazer a pesquisa
        if (!empty($data['s'])) {
            echo json_encode($data);
        }
    }

    /**
     * @param  array  $data
     */
    public function blogPost(array $data): void
    {
        //o nome da variável da rota em Route->get
        $post = (new Post())->findByUri($data['uri']);
        if(!$post){
            redirect("/404");
        }
        
        $post->views += 1;
        $post->save();                                      

        $head = $this->seo->render(
            "{$post->title}" . CONF_SITE_NAME,
            $post->subtitle,
            url("/blog/{$post->uri}"),
            image($post->cover, 1200, 628)
        );

        echo $this->view->render("blog-post", [
            "head" => $head,
            "post" => $post,
            "related" => (new Post())
            ->find("category = :c AND id != :i", "c={$post->category}&i={$post->id}")
            ->order("rand()")//sempre que o usuario carregar as paginas, vai ter resultados diferentes
            ->limit(3)
            ->fetch(true)
        ]);
    }

    /**
     * SITE LOGIN
     */
    public function login(): void
    {
        $head = $this->seo->render(
            "Entrar - " . CONF_SITE_NAME,
            CONF_SITE_DESC,
            url("/entrar"),
            theme("/assets/images/share.jpg")
        );

        echo $this->view->render("auth-login", [
            "head" => $head,
        ]);
    }

    /**
     * SITE FORGET
     */
    public function forget(): void
    {
        $head = $this->seo->render(
            "Recuperar Senha - " . CONF_SITE_NAME,
            CONF_SITE_DESC,
            url("/recuperar"),
            theme("/assets/images/share.jpg")
        );

        echo $this->view->render("auth-forget", [
            "head" => $head,
        ]);
    }

    /**
     * SITE REGISTER
     */
    public function register(): void
    {
        $head = $this->seo->render(
            "Crirar Conta - " . CONF_SITE_NAME,
            CONF_SITE_DESC,
            url("/cadastrar"),
            theme("/assets/images/share.jpg")
        );

        echo $this->view->render("auth-register", [
            "head" => $head,
        ]);
    }

    /**
     * SITE CONFIRM
     */
    public function confirm()
    {
        $head = $this->seo->render(
            "Confirme Seu Cadastro - " . CONF_SITE_NAME,
            CONF_SITE_DESC,
            url("/confirma"),
            theme("/assets/images/share.jpg")
        );

        echo $this->view->render("optin-confirm", [
            "head" => $head,
        ]);
    }

    /**
     * SITE SUCCESS
     */
    public function success()
    {
        $head = $this->seo->render(
            "Bem-Vindo(ao) " . CONF_SITE_NAME,
            CONF_SITE_DESC,
            url("/obrigado"),
            theme("/assets/images/share.jpg")
        );

        echo $this->view->render("optin-success", [
            "head" => $head,
        ]);
    }


    /**
     * Site Terms
     */
    public function terms(): void
    {
        $head = $this->seo->render(
            CONF_SITE_NAME . " - " . "Termos de uso",
            CONF_SITE_DESC,
            url("/termos"),
            theme("/assets/images/share.jpg")
        );

        echo $this->view->render("terms", [
            "head" => $head,
        ]);
    }

    /**
     * site nav error
     * @param  array  $data
     */
    public function error(array $data): void
    {
        $error = new \stdClass();

        //Fazer testes de error
        switch ($data['errcode']) {
            //erro de conexao
            case "problemas":
                $error->code = "OPS";
                $error->title = "Estamos enfrentando problemas!";
                $error->message = "Parece que o nosso serviço não está disponível no momento. Já estamos vendo isso, mas caso precise envie um e-mail :)";
                $error->linkTitle = "ENVIAR E-MAIL";
                $error->link = "mailto:" . CONF_MAIL_SUPPORT;
                break;

            case "manutencao":
                $error->code = "OPS";
                $error->title = "Desculpe. Estamos em manutenção";
                $error->message = "Voltamos logo! Por hora estamos trabalhando para melhorar nosso conteúdo para você controlar melhor as suas contas :p";
                $error->linkTitle = null;
                $error->link = null;
                break;

                //erros de navegação
            default:
                $error->code = $data["errcode"];
                $error->title = "Ooops. Conteúdo indisponível! :/";
                //msg genérica que me permite trabalhar com mais de um tipo de erro dentro da requisição http
                $error->message = "Sentimos muito, mas o conteúdo que você tentou acessar não existe, está indisponível no momento ou foi removido :/";
                $error->linkTitle = "Continue navegando!";
                //levar o usuário para página anterior de onde veio, ou para a home
                $error->link = url_back();
                break;
        }

        $head = $this->seo->render(
            "{$error->code} | {$error->title}",
            $error->message,
                //como estamos trabalhando no SEO, temos que passar a url que ta gerando a mensagem
            url("/ops/{$error->code}"),
            theme("/assets/images/share.jpg"),
            false // nenhum mecanismo de pesquisa vai indexar
        );

        echo $this->view->render("error", [
            "head" => $head,
            "error" => $error
        ]);
    }
}
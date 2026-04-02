<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../app/core/Autoloader.php';
Autoloader::register();

class EvaluationControllerTest extends TestCase
{
    protected function setUp(): void
    {
        if (!defined('PHPUNIT_RUNNING')) {
            define('PHPUNIT_RUNNING', true);
        }

        $_SESSION = [];
        $_POST = [];
        $_SERVER = [];
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $_SESSION['csrf_token'] = 'test_token';
        $_SESSION['user'] = ['id' => 1];
    }

    /**
     * 🔹 GET /evaluation/create/{id}
     * → doit afficher le formulaire
     */
    public function testCreateGet()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SESSION['user']['id'] = 1;
        $_SESSION['csrf_token'] = 'token';

        // Mock Entreprise
        $entrepriseMock = $this->createMock(Entreprise::class);
        $entrepriseMock->method('findById')->willReturn([
            'id_entreprise' => 1,
            'nom' => 'Test'
        ]);

        // Mock Evaluation
        $evaluationMock = $this->createMock(Evaluation::class);
        $evaluationMock->method('findBy')->willReturn([]);

        // Injection via override
        $controller = $this->getMockBuilder(EvaluationController::class)
            ->onlyMethods(['doRedirect', 'render', 'getEntrepriseModel', 'getEvaluationModel'])
            ->getMock();

        $controller->method('getEntrepriseModel')->willReturn($entrepriseMock);
        $controller->method('getEvaluationModel')->willReturn($evaluationMock);

        // inject mocks
        $this->injectModel($controller, 'Entreprise', $entrepriseMock);
        $this->injectModel($controller, 'Evaluation', $evaluationMock);

        $controller->expects($this->once())
            ->method('render')
            ->with(
                'evaluation/create',
                $this->callback(function ($data) {
                    return isset($data['entreprise'])
                        && isset($data['csrf_token']);
                })
            );

        $controller->create(1);
    }

    /**
     * 🔹 POST invalide → erreurs → render
     */
    public function testCreatePostInvalid()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['user']['id'] = 1;
        $_SESSION['csrf_token'] = 'token';

        $_POST = [
            'csrf_token' => 'token',
            'commentaire' => '' // invalide
        ];

        $entrepriseMock = $this->createMock(Entreprise::class);
        $entrepriseMock->method('findById')->willReturn([
            'id_entreprise' => 1
        ]);

        $evaluationMock = $this->createMock(Evaluation::class);
        $evaluationMock->method('findBy')->willReturn([]);

        $controller = $this->getMockBuilder(EvaluationController::class)
            ->onlyMethods(['doRedirect', 'render', 'getEntrepriseModel', 'getEvaluationModel'])
            ->getMock();

        $controller->method('getEntrepriseModel')->willReturn($entrepriseMock);
        $controller->method('getEvaluationModel')->willReturn($evaluationMock);

        $this->injectModel($controller, 'Entreprise', $entrepriseMock);
        $this->injectModel($controller, 'Evaluation', $evaluationMock);

        $controller->expects($this->once())
            ->method('render')
            ->with(
                'evaluation/create',
                $this->callback(function ($data) {
                    return !empty($data['errors']);
                })
            );

        $controller->create(1);
    }

    /**
     * 🔹 POST valide → evaluate + redirect
     */
    public function testCreatePostValid()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['user']['id'] = 1;
        $_SESSION['csrf_token'] = 'token';

        $_POST = [
            'csrf_token' => 'token',
            'commentaire' => 'OK',
            'evaluation' => 5
        ];

        $entrepriseMock = $this->createMock(Entreprise::class);
        $entrepriseMock->method('findById')->willReturn([
            'id_entreprise' => 1
        ]);

        $evaluationMock = $this->createMock(Evaluation::class);
        $evaluationMock->method('findBy')->willReturn([]);

        $evaluationMock->expects($this->once())
            ->method('evaluate')
            ->with(1, 5, 'OK');

        $controller = $this->getMockBuilder(EvaluationController::class)
            ->onlyMethods(['doRedirect', 'render', 'getEntrepriseModel', 'getEvaluationModel'])
            ->getMock();

        $controller->method('getEntrepriseModel')->willReturn($entrepriseMock);
        $controller->method('getEvaluationModel')->willReturn($evaluationMock);

        $this->injectModel($controller, 'Entreprise', $entrepriseMock);
        $this->injectModel($controller, 'Evaluation', $evaluationMock);

        $controller->expects($this->once())
            ->method('doRedirect')
            ->with('/entreprise/recherche');

        $controller->create(1);
    }

    /**
     * 🔹 show GET
     */
    public function testShowGet()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SESSION['csrf_token'] = 'token';

        $evaluationMock = $this->createMock(Evaluation::class);

        $evaluationMock->method('moyenne')->willReturn([
            'moyenne' => 4.2,
            'nbre' => 10
        ]);

        $evaluationMock->method('search')->willReturn([
            'results' => [],
            'total' => 0
        ]);

        $controller = $this->getMockBuilder(EvaluationController::class)
            ->onlyMethods(['doRedirect', 'render', 'getEntrepriseModel', 'getEvaluationModel'])
            ->getMock();

        $controller->method('getEntrepriseModel')->willReturn($entrepriseMock);
        $controller->method('getEvaluationModel')->willReturn($evaluationMock);

        $this->injectModel($controller, 'Evaluation', $evaluationMock);

        $controller->expects($this->once())
            ->method('render')
            ->with(
                'evaluation/show',
                $this->callback(function ($data) {
                    return isset($data['results'])
                        && isset($data['note'])
                        && isset($data['pagination']);
                })
            );

        $controller->show(1);
    }

    /**
     * 🔧 Injection simplifiée des modèles
     */
    private function injectModel($controller, $className, $mock)
    {
        // hack simple : surcharge new Class()
        // nécessite que tu adaptes ton controller si besoin
        $controller->$className = $mock;
    }
}

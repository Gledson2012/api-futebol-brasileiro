<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBrasileiraoJogosDetalhesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('brasileirao_jogos_detalhes', function (Blueprint $table) {
            $table->id();

            $table->string("codigo_referencia_jogo")->unique()->nullable(false)->comment("Armazena o código de referência do jogo.");

            $table->string("nome_estadio")->nullable(false)->comment("Nome do estádio que aconteceu o jogo.");
            $table->dateTime("data_hora_jogo")->nullable(false)->comment("Informações de data e hora que aconteceu o jogo.");

            $table->string("equipa_casa")->nullable(false)->comment("Nome do equipa que está que joga em casa.");
            $table->integer("equipa_casa_gols")->nullable(false)->comment("Gols do equipa que está que joga em casa.");
            $table->string("equipa_casa_posse_bola")->nullable(false)->comment("Posse de bola do equipa que está que joga em casa.");
            $table->integer("equipa_casa_cartoes_amarelos")->nullable(false)->default(0)->comment("Quantidade de cartões amarelos do equipa que está que joga em casa.");
            $table->integer("equipa_casa_cartoes_vermelhos")->nullable(false)->default(0)->comment("Quantidade de cartões vermelhos do equipa que está que joga em casa.");
            $table->json("equipa_casa_escalacao")->nullable(false)->comment("Escalação do equipa que está que joga em casa.");

            $table->string("equipa_visitante")->nullable(false)->comment("Nome do equipa que está que joga fora.");
            $table->integer("equipa_visitante_gols")->nullable(false)->comment("Gols do equipa que está que joga fora.");
            $table->string("equipa_visitante_posse_bola")->nullable(false)->comment("Posse de bola do equipa que está que joga fora.");
            $table->integer("equipa_visitante_cartoes_amarelos")->nullable(false)->default(0)->comment("Quantidade de cartões amarelos do equipa que está que joga fora.");
            $table->integer("equipa_visitante_cartoes_vermelhos")->nullable(false)->default(0)->comment("Quantidade de cartões vermelhos do equipa que está que joga fora.");
            $table->json("equipa_visitante_escalacao")->nullable(false)->comment("Escalação do equipa que está que joga fora.");

            $table->json("estatisticas")->nullable(false)->comment("Armazena detalhes da partida, quantidade de escanteio, impedimentos, faltas, chutes a gols e dentre outras.");

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('brasileirao_jogos_detalhes');
    }
}

<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <https://www.gnu.org/licenses/>.

/**
 * Backup drill strings.
 *
 * @package    local_backupdrill
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$string['actions'] = 'Ações';
$string['actual'] = 'Observado';
$string['alertbody'] = 'O teste de backup {$a->runid} terminou com estado {$a->status}. Consulte o painel protegido do laboratório.';
$string['alertsubject'] = 'Falha no teste de backup';
$string['alreadyrunning'] = 'Esta execução não está pendente.';
$string['archivesizelimit'] = 'O backup extraído excede o limite de espaço.';
$string['attemptsexceeded'] = 'Número máximo de tentativas excedido.';
$string['autocleanup'] = 'Excluir cursos temporários pertencentes ao plugin após o teste';
$string['back'] = 'Voltar';
$string['check'] = 'Verificação';
$string['cleanupdenied'] = 'A limpeza exige autorização explícita.';
$string['cleanupfailed'] = 'A limpeza do curso temporário falhou.';
$string['compare'] = 'Comparar';
$string['comparewith'] = 'Comparar com a execução ID';
$string['comparison'] = 'Comparação de execuções';
$string['current'] = 'Atual';
$string['dashboard'] = 'Testes de restauração de backups';
$string['date'] = 'Data';
$string['deadlineexceeded'] = 'O tempo máximo de execução foi excedido.';
$string['disable'] = 'Desativar agendamento';
$string['enable'] = 'Ativar agendamento';
$string['enabled'] = 'Executar automaticamente conforme agendamento';
$string['errorcode'] = 'Código de erro';
$string['eventartifact_deleted'] = 'Curso temporário de teste removido';
$string['eventdrill_completed'] = 'Teste de backup concluído';
$string['eventplan_created'] = 'Plano de teste de backup criado';
$string['existingfile'] = 'MBZ existente no laboratório (opcional)';
$string['expected'] = 'Esperado';
$string['failed'] = 'Reprovados';
$string['fingerprint'] = 'Impressão digital física do ambiente autorizado';
$string['history'] = 'Histórico de execuções';
$string['intervalhours'] = 'Intervalo (horas)';
$string['invalidarchive'] = 'O arquivo ou manifesto do backup é inválido.';
$string['invalidplan'] = 'O plano de teste ou seus limites de recursos são inválidos.';
$string['invalidrun'] = 'Registro de execução inválido.';
$string['labblocked'] = 'Restauração bloqueada: esta instalação não está explicitamente cadastrada como laboratório isolado da rede.';
$string['labname'] = 'Nome do laboratório';
$string['labverified'] = 'Identidade do laboratório verificada:';
$string['lockunavailable'] = 'Outro teste já está utilizando este backup.';
$string['maxseconds'] = 'Tempo máximo de execução (segundos)';
$string['missingplugin'] = 'Um componente necessário do backup está ausente no laboratório.';
$string['newplan'] = 'Novo plano de restauração';
$string['nocategory'] = 'Não existe categoria disponível para cursos temporários.';
$string['passed'] = 'Aprovados';
$string['pending'] = 'Pendentes';
$string['planname'] = 'Nome do plano';
$string['plans'] = 'Planos';
$string['pluginname'] = 'Backup Drill';
$string['previous'] = 'Anterior';
$string['privacy:metadata:report'] = 'Resultados técnicos dos testes que não incluem informações individuais de usuários.';
$string['privacy:metadata:runs'] = 'Histórico de execuções e evidências de validação.';
$string['privacy:metadata:source'] = 'Arquivos de backup enviados podem conter dados pessoais dos cursos originais.';
$string['queued'] = 'A execução {$a} foi enfileirada.';
$string['report'] = 'Relatório técnico';
$string['restoreprecheckfailed'] = 'As verificações prévias da restauração do Moodle falharam.';
$string['retentiondays'] = 'Retenção do histórico concluído (dias)';
$string['runnow'] = 'Enfileirar teste';
$string['saveplan'] = 'Salvar plano';
$string['settings'] = 'Configurações do Backup Drill';
$string['sourcefile'] = 'Backup Moodle (.mbz)';
$string['status'] = 'Estado';
$string['taskexecute'] = 'Executar teste de restauração de backup';
$string['taskprune'] = 'Limpar histórico expirado dos testes';
$string['taskschedule'] = 'Agendar testes de restauração de backups';
$string['unsafeenvironment'] = 'Ambiente não verificado: restauração ou limpeza proibida.';
$string['uploadnew'] = 'Enviar novo backup';
$string['warnings'] = 'Com avisos / não verificados';

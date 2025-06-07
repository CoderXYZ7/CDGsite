data-search="<?= htmlspecialchars(strtolower($anim['Nome'] . ' ' . $anim['Cognome'] . ' ' . $anim['LaboratorioNome'])) ?>">
                        <td><?= htmlspecialchars($anim['Nome']) ?></td>
                        <td><?= htmlspecialchars($anim['Cognome']) ?></td>
                        <td><?= htmlspecialchars($anim['LaboratorioNome']) ?></td>
                        <td><span class="badge badge-fascia-<?= strtolower($anim['Fascia']) ?>"><?= $anim['Fascia'] ?></span></td>
                        <td>
                            <?php if ($anim['Colore'] != 'X'): ?>
                            <span class="badge badge-colore-<?= strtolower($anim['Colore']) ?>"><?= $anim['Colore'] ?></span>
                            <?php else: ?>
                            <span class="badge badge-gray">Non assegnato</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                            $categorie = [];
                            if ($anim['M'] == 'M') $categorie[] = 'Mini';
                            if ($anim['J'] == 'J') $categorie[] = 'Juniores';
                            if ($anim['S'] == 'S') $categorie[] = 'Seniores';
                            echo implode(', ', $categorie) ?: 'Nessuna';
                            ?>
                        </td>
                        <td>
                            <?php
                            $resp_animatore = array_filter($animatori_responsabili, fn($r) => $r['AnimatoreNome'] == $anim['Nome'] && $r['AnimatoreCognome'] == $anim['Cognome']);
                            $nomi_resp = array_map(fn($r) => $r['ResponsabileNome'], $resp_animatore);
                            echo implode(', ', $nomi_resp) ?: 'Nessuno';
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
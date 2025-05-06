// ✅ Start Turbo
import { start, renderStreamMessage } from '@hotwired/turbo';
start();

// ✅ Expose Turbo and renderStreamMessage globally (if needed)
import * as Turbo from '@hotwired/turbo';
window.Turbo = Turbo;
window.renderStreamMessage = renderStreamMessage; // 👈 Add this line

// ✅ Stimulus setup
import { Application } from '@hotwired/stimulus';
import ClaimActionToggleController from './controllers/claim_action_toggle_controller';

const application = Application.start();
application.register('claim-action-toggle', ClaimActionToggleController);

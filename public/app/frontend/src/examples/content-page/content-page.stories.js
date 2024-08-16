import civilProcedureRules from './civil-procedure-rules.html.twig';
import standardDirections from './standard-directions.html.twig';

export default {
    title: 'Example pages/Content page',
    parameters: {
        layout: 'fullscreen',
    },
};

const CPRTemplate = (args) => {
    return civilProcedureRules(args);
};
const SDTemplate = (args) => {
    return standardDirections(args);
};

export const CivilProcedureRules = CPRTemplate.bind({});
CivilProcedureRules.args = {};

export const StandardDirections = SDTemplate.bind({});
StandardDirections.args = {};

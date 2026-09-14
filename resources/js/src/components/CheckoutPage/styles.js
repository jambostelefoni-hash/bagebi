import { makeStyles } from '@material-ui/core/styles';

export default makeStyles(theme => ({
  formShell: { padding: theme.spacing(1, 1, 0) },
  intro: { display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 16, margin: theme.spacing(1, 0, 2), padding: theme.spacing(0, 1) },
  introCopy: { '& strong': { display: 'block', color: '#14213d', fontSize: 14, fontWeight: 800 }, '& span': { display: 'block', marginTop: 4, color: '#7a8598', fontSize: 10 } },
  progressText: { flex: '0 0 auto', padding: '7px 11px', borderRadius: 20, background: '#edf1ff', color: '#3157d5', fontSize: 10, fontWeight: 800 },
  stepper: {
    padding: theme.spacing(2.5, 1, 3.5), background: 'transparent', borderBottom: '1px solid #e8ecf3', marginBottom: theme.spacing(3),
    '& .MuiStepLabel-label': { marginTop: 7, color: '#8993a5', fontSize: 10, fontWeight: 700 },
    '& .MuiStepLabel-label.MuiStepLabel-active': { color: '#14213d', fontWeight: 800 },
    '& .MuiStepIcon-root': { width: 29, height: 29, color: '#e2e7f0' },
    '& .MuiStepIcon-root.MuiStepIcon-active': { color: '#3157d5', filter: 'drop-shadow(0 5px 8px rgba(49,87,213,.25))' },
    '& .MuiStepIcon-root.MuiStepIcon-completed': { color: '#21b887' },
    '& .MuiStepConnector-line': { borderColor: '#e2e7f0' }
  },
  section: { minHeight: 290, padding: theme.spacing(0, 1) },
  buttons: { display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginTop: theme.spacing(3), padding: theme.spacing(2.5, 1, 0), borderTop: '1px solid #e8ecf3' },
  buttonPrimary: { minWidth: 135, minHeight: 46, marginLeft: 'auto', borderRadius: 12, textTransform: 'none', fontSize: 12, fontWeight: 800, boxShadow: '0 10px 22px rgba(49,87,213,.24)', '&:hover': { boxShadow: '0 13px 26px rgba(49,87,213,.3)' } },
  buttonSecondary: { minWidth: 105, minHeight: 46, borderRadius: 12, textTransform: 'none', fontSize: 12, fontWeight: 700, border: '1px solid #dbe1ec', color: '#536078' },
  wrapper: { marginLeft: 'auto', position: 'relative' },
  buttonProgress: { position: 'absolute', top: '50%', left: '50%', marginTop: -12, marginLeft: -12, color: '#fff' },
  errorBox: { margin: theme.spacing(0, 1, 2), padding: theme.spacing(1.5, 2), borderRadius: 11, background: '#fff0f0', color: '#ad3f3f', fontSize: 11, fontWeight: 600 },
  closed: { padding: theme.spacing(5, 3), textAlign: 'center', borderRadius: 16, background: '#fff7e6', color: '#835f19', fontSize: 14, fontWeight: 700 },
  [theme.breakpoints.down('xs')]: {
    formShell: { padding: 0 }, intro: { alignItems: 'flex-start', flexDirection: 'column' },
    stepper: { padding: theme.spacing(2, 0, 3), '& .MuiStepLabel-label': { fontSize: 8 }, '& .MuiStepIcon-root': { width: 24, height: 24 } },
    section: { minHeight: 0, padding: 0 }, buttons: { paddingLeft: 0, paddingRight: 0 }, buttonPrimary: { minWidth: 115 }, buttonSecondary: { minWidth: 90 }
  }
}));

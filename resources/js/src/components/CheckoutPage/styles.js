import { makeStyles } from '@material-ui/core/styles';
export default makeStyles(theme => ({
  stepper: {
    padding: theme.spacing(2, 0, 3),
    background: 'transparent',
    '& .MuiStepIcon-root.MuiStepIcon-active': {
      color: theme.palette.primary.main
    },
    '& .MuiStepIcon-root.MuiStepIcon-completed': {
      color: theme.palette.primary.main
    }
  },
  buttons: {
    display: 'flex',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginTop: theme.spacing(1)
  },
  buttonPrimary: {
    marginTop: theme.spacing(2),
    padding: theme.spacing(1, 3),
    borderRadius: 10,
    textTransform: 'none',
    fontWeight: 600,
    boxShadow: '0 8px 16px rgba(15, 23, 42, 0.12)'
  },
  buttonSecondary: {
    marginTop: theme.spacing(2),
    padding: theme.spacing(1, 3),
    borderRadius: 10,
    textTransform: 'none',
    fontWeight: 600,
    border: `1px solid ${theme.palette.grey[300]}`,
    color: theme.palette.text.primary
  },
  wrapper: {
    margin: theme.spacing(1),
    position: 'relative'
  },
  buttonProgress: {
    position: 'absolute',
    top: '50%',
    left: '50%'
  },
  pageTitle: {
    fontFamily: "'Sora', 'Manrope', 'Segoe UI', Arial, sans-serif",
    fontWeight: 700,
    letterSpacing: -0.4,
    marginBottom: theme.spacing(1)
  },
  pageSubtitle: {
    color: theme.palette.text.secondary,
    marginBottom: theme.spacing(2)
  },
  colorRed: {
    color: '#b91c1c',
    fontWeight: 600
  }
}));


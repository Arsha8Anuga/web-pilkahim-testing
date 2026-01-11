<?php

namespace App\Enums;

enum AuditLogAction: string
{
    case LOGIN_SUCCESS        = 'LOGIN_SUCCESS';
    case LOGIN_FAILED         = 'LOGIN_FAILED';
    case LOGOUT               = 'LOGOUT';
    case VOTE_CAST            = 'VOTE_CAST';
    case VOTE_REJECTED        = 'VOTE_REJECTED';
    case ELECTION_CREATED     = 'ELECTION_CREATED';
    case ELECTION_ACTIVATED   = 'ELECTION_ACTIVATED';
    case ELECTION_CLOSED      = 'ELECTION_CLOSED';
    case ELECTION_DELETED     = 'ELECTION_DELETED';
    case CANDIDATE_CREATED    = 'CANDIDATE_CREATED';
    case CANDIDATE_DELETED    = 'CANDIDATE_DELETED';
    case CONFIG_CHANGED       = 'CONFIG_CHANGED';
}

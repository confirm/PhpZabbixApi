<?php

/*
 * This file is part of PhpZabbixApi.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @copyright The MIT License (MIT)
 * @author confirm IT solutions GmbH, Rathausstrase 14, CH-6340 Baar
 */

namespace Confirm\ZabbixApi;

use Psr\Http\Message\ResponseInterface;

interface ZabbixApiInterface
{
    const PHP_ZABBIX_API_VERSION = '3';

    const UNAUTHORIZED_ERROR_CODE = -32602;

    const UNAUTHORIZED_ERROR_MESSAGE = 'Not authorised.';

    const UNAUTHORIZED_SESSION_TERMINATED_ERROR_MESSAGE = 'Session terminated, re-login, please.';

    const ACCESS_DENY_OBJECT = 0;

    const ACCESS_DENY_PAGE = 1;

    const ACTION_DEFAULT_MSG_AUTOREG = "Host name: {HOST.HOST}\nHost IP: {HOST.IP}\nAgent port: {HOST.PORT}";

    const ACTION_DEFAULT_SUBJ_AUTOREG = 'Auto registration: {HOST.HOST}';

    const ACTION_DEFAULT_SUBJ_DISCOVERY = 'Discovery: {DISCOVERY.DEVICE.STATUS} {DISCOVERY.DEVICE.IPADDRESS}';

    const ACTION_DEFAULT_SUBJ_TRIGGER = '{TRIGGER.STATUS}: {TRIGGER.NAME}';

    const ACTION_STATUS_DISABLED = 1;

    const ACTION_STATUS_ENABLED = 0;

    const ALERT_MAX_RETRIES = 3;

    const ALERT_STATUS_FAILED = 2;

    const ALERT_STATUS_NOT_SENT = 0;

    const ALERT_STATUS_SENT = 1;

    const ALERT_TYPE_COMMAND = 1;

    const ALERT_TYPE_MESSAGE = 0;

    const API_OUTPUT_COUNT = 'count';

    const API_OUTPUT_EXTEND = 'extend';

    const AUDIT_ACTION_ADD = 0;

    const AUDIT_ACTION_DELETE = 2;

    const AUDIT_ACTION_DISABLE = 6;

    const AUDIT_ACTION_ENABLE = 5;

    const AUDIT_ACTION_LOGIN = 3;

    const AUDIT_ACTION_LOGOUT = 4;

    const AUDIT_ACTION_UPDATE = 1;

    const AUDIT_RESOURCE_ACTION = 5;

    const AUDIT_RESOURCE_APPLICATION = 12;

    const AUDIT_RESOURCE_DISCOVERY_RULE = 23;

    const AUDIT_RESOURCE_GRAPH = 6;

    const AUDIT_RESOURCE_GRAPH_ELEMENT = 7;

    const AUDIT_RESOURCE_HOST = 4;

    const AUDIT_RESOURCE_HOST_GROUP = 14;

    const AUDIT_RESOURCE_IMAGE = 16;

    const AUDIT_RESOURCE_ITEM = 15;

    const AUDIT_RESOURCE_IT_SERVICE = 18;

    const AUDIT_RESOURCE_MACRO = 29;

    const AUDIT_RESOURCE_MAINTENANCE = 27;

    const AUDIT_RESOURCE_MAP = 19;

    const AUDIT_RESOURCE_MEDIA_TYPE = 3;

    const AUDIT_RESOURCE_PROXY = 26;

    const AUDIT_RESOURCE_REGEXP = 28;

    const AUDIT_RESOURCE_SCENARIO = 22;

    const AUDIT_RESOURCE_SCREEN = 20;

    const AUDIT_RESOURCE_SCRIPT = 25;

    const AUDIT_RESOURCE_SLIDESHOW = 24;

    const AUDIT_RESOURCE_TEMPLATE = 30;

    const AUDIT_RESOURCE_TRIGGER = 13;

    const AUDIT_RESOURCE_TRIGGER_PROTOTYPE = 31;

    const AUDIT_RESOURCE_USER = 0;

    const AUDIT_RESOURCE_USER_GROUP = 11;

    const AUDIT_RESOURCE_VALUE_MAP = 17;

    const AUDIT_RESOURCE_ZABBIX_CONFIG = 2;

    const AVAILABILITY_REPORT_BY_HOST = 0;

    const AVAILABILITY_REPORT_BY_TEMPLATE = 1;

    const BR_COMPARE_VALUE_MULTIPLE_PERIODS = 3;

    const BR_DISTRIBUTION_MULTIPLE_ITEMS = 2;

    const BR_DISTRIBUTION_MULTIPLE_PERIODS = 1;

    const CALC_FNC_ALL = 7;

    const CALC_FNC_AVG = 2;

    const CALC_FNC_LST = 9;

    const CALC_FNC_MAX = 4;

    const CALC_FNC_MIN = 1;

    const CONDITION_EVAL_TYPE_AND = 1;

    const CONDITION_EVAL_TYPE_AND_OR = 0;

    const CONDITION_EVAL_TYPE_EXPRESSION = 3;

    const CONDITION_EVAL_TYPE_OR = 2;

    const CONDITION_OPERATOR_EQUAL = 0;

    const CONDITION_OPERATOR_IN = 4;

    const CONDITION_OPERATOR_LESS_EQUAL = 6;

    const CONDITION_OPERATOR_LIKE = 2;

    const CONDITION_OPERATOR_MORE_EQUAL = 5;

    const CONDITION_OPERATOR_NOT_EQUAL = 1;

    const CONDITION_OPERATOR_NOT_IN = 7;

    const CONDITION_OPERATOR_NOT_LIKE = 3;

    const CONDITION_OPERATOR_REGEXP = 8;

    const CONDITION_TYPE_APPLICATION = 15;

    const CONDITION_TYPE_DCHECK = 19;

    const CONDITION_TYPE_DHOST_IP = 7;

    const CONDITION_TYPE_DOBJECT = 21;

    const CONDITION_TYPE_DRULE = 18;

    const CONDITION_TYPE_DSERVICE_PORT = 9;

    const CONDITION_TYPE_DSERVICE_TYPE = 8;

    const CONDITION_TYPE_DSTATUS = 10;

    const CONDITION_TYPE_DUPTIME = 11;

    const CONDITION_TYPE_DVALUE = 12;

    const CONDITION_TYPE_EVENT_ACKNOWLEDGED = 14;

    const CONDITION_TYPE_EVENT_TYPE = 23;

    const CONDITION_TYPE_HOST = 1;

    const CONDITION_TYPE_HOST_GROUP = 0;

    const CONDITION_TYPE_HOST_METADATA = 24;

    const CONDITION_TYPE_HOST_NAME = 22;

    const CONDITION_TYPE_MAINTENANCE = 16;

    const CONDITION_TYPE_PROXY = 20;

    const CONDITION_TYPE_TEMPLATE = 13;

    const CONDITION_TYPE_TIME_PERIOD = 6;

    const CONDITION_TYPE_TRIGGER = 2;

    const CONDITION_TYPE_TRIGGER_NAME = 3;

    const CONDITION_TYPE_TRIGGER_SEVERITY = 4;

    const CONDITION_TYPE_TRIGGER_VALUE = 5;

    const COPY_TYPE_TO_HOST = 0;

    const COPY_TYPE_TO_HOST_GROUP = 1;

    const COPY_TYPE_TO_TEMPLATE = 2;

    const DATE_FORMAT_CONTEXT = 'Date format (see http://php.net/date)';

    const DATE_TIME_FORMAT_SECONDS_XML = 'Y-m-d\TH:i:s\Z';

    const DAY_IN_YEAR = 365;

    const DB_ID = "({}>=0&&bccomp({},\"9223372036854775807\")<=0)&&";

    const DEFAULT_LATEST_ISSUES_CNT = 20;

    const DHOST_STATUS_ACTIVE = 0;

    const DHOST_STATUS_DISABLED = 1;

    const DOBJECT_STATUS_DISCOVER = 2;

    const DOBJECT_STATUS_DOWN = 1;

    const DOBJECT_STATUS_LOST = 3;

    const DOBJECT_STATUS_UP = 0;

    const DRULE_STATUS_ACTIVE = 0;

    const DRULE_STATUS_DISABLED = 1;

    const DSVC_STATUS_ACTIVE = 0;

    const DSVC_STATUS_DISABLED = 1;

    const EVENTS_OPTION_ALL = 2;

    const EVENTS_OPTION_NOEVENT = 1;

    const EVENTS_OPTION_NOT_ACK = 3;

    const EVENT_ACKNOWLEDGED = '1';

    const EVENT_ACK_DISABLED = '0';

    const EVENT_ACK_ENABLED = '1';

    const EVENT_NOT_ACKNOWLEDGED = '0';

    const EVENT_OBJECT_AUTOREGHOST = 3;

    const EVENT_OBJECT_DHOST = 1;

    const EVENT_OBJECT_DSERVICE = 2;

    const EVENT_OBJECT_ITEM = 4;

    const EVENT_OBJECT_LLDRULE = 5;

    const EVENT_OBJECT_TRIGGER = 0;

    const EVENT_SOURCE_AUTO_REGISTRATION = 2;

    const EVENT_SOURCE_DISCOVERY = 1;

    const EVENT_SOURCE_INTERNAL = 3;

    const EVENT_SOURCE_TRIGGERS = 0;

    const EVENT_TYPE_ITEM_NORMAL = 1;

    const EVENT_TYPE_ITEM_NOTSUPPORTED = 0;

    const EVENT_TYPE_LLDRULE_NORMAL = 3;

    const EVENT_TYPE_LLDRULE_NOTSUPPORTED = 2;

    const EVENT_TYPE_TRIGGER_NORMAL = 5;

    const EVENT_TYPE_TRIGGER_UNKNOWN = 4;

    const EXPRESSION_FUNCTION_UNKNOWN = '#ERROR_FUNCTION#';

    const EXPRESSION_HOST_ITEM_UNKNOWN = '#ERROR_ITEM#';

    const EXPRESSION_HOST_UNKNOWN = '#ERROR_HOST#';

    const EXPRESSION_NOT_A_MACRO_ERROR = '#ERROR_MACRO#';

    const EXPRESSION_TYPE_ANY_INCLUDED = 1;

    const EXPRESSION_TYPE_FALSE = 4;

    const EXPRESSION_TYPE_INCLUDED = 0;

    const EXPRESSION_TYPE_NOT_INCLUDED = 2;

    const EXPRESSION_TYPE_TRUE = 3;

    const EXTACK_OPTION_ALL = 0;

    const EXTACK_OPTION_BOTH = 2;

    const EXTACK_OPTION_UNACK = 1;

    const EZ_TEXTING_LIMIT_CANADA = 1;

    const EZ_TEXTING_LIMIT_USA = 0;

    const FILTER_TASK_HIDE = 1;

    const FILTER_TASK_INVERT_MARK = 3;

    const FILTER_TASK_MARK = 2;

    const FILTER_TASK_SHOW = 0;

    const GRAPH_3D_ANGLE = 70;

    const GRAPH_ITEM_DRAWTYPE_BOLD_DOT = 6;

    const GRAPH_ITEM_DRAWTYPE_BOLD_LINE = 2;

    const GRAPH_ITEM_DRAWTYPE_DASHED_LINE = 4;

    const GRAPH_ITEM_DRAWTYPE_DOT = 3;

    const GRAPH_ITEM_DRAWTYPE_FILLED_REGION = 1;

    const GRAPH_ITEM_DRAWTYPE_GRADIENT_LINE = 5;

    const GRAPH_ITEM_DRAWTYPE_LINE = 0;

    const GRAPH_ITEM_SIMPLE = 0;

    const GRAPH_ITEM_SUM = 2;

    const GRAPH_STACKED_ALFA = 15;

    const GRAPH_TRIGGER_LINE_OPPOSITE_COLOR = '000000';

    const GRAPH_TYPE_3D = 4;

    const GRAPH_TYPE_3D_EXPLODED = 5;

    const GRAPH_TYPE_BAR = 6;

    const GRAPH_TYPE_BAR_STACKED = 8;

    const GRAPH_TYPE_COLUMN = 7;

    const GRAPH_TYPE_COLUMN_STACKED = 9;

    const GRAPH_TYPE_EXPLODED = 3;

    const GRAPH_TYPE_NORMAL = 0;

    const GRAPH_TYPE_PIE = 2;

    const GRAPH_TYPE_STACKED = 1;

    const GRAPH_YAXIS_SIDE_DEFAULT = 0;

    const GRAPH_YAXIS_SIDE_LEFT = 0;

    const GRAPH_YAXIS_SIDE_RIGHT = 1;

    const GRAPH_YAXIS_TYPE_CALCULATED = 0;

    const GRAPH_YAXIS_TYPE_FIXED = 1;

    const GRAPH_YAXIS_TYPE_ITEM_VALUE = 2;

    const GRAPH_ZERO_LINE_COLOR_LEFT = 'AAAAAA';

    const GRAPH_ZERO_LINE_COLOR_RIGHT = '888888';

    const GROUP_DEBUG_MODE_DISABLED = 0;

    const GROUP_DEBUG_MODE_ENABLED = 1;

    const GROUP_GUI_ACCESS_DISABLED = 2;

    const GROUP_GUI_ACCESS_INTERNAL = 1;

    const GROUP_GUI_ACCESS_SYSTEM = 0;

    const GROUP_STATUS_DISABLED = 1;

    const GROUP_STATUS_ENABLED = 0;

    const HALIGN_CENTER = 0;

    const HALIGN_DEFAULT = 0;

    const HALIGN_LEFT = 1;

    const HALIGN_RIGHT = 2;

    const HISTORY_BATCH_GRAPH = 'batchgraph';

    const HISTORY_GRAPH = 'showgraph';

    const HISTORY_LATEST = 'showlatest';

    const HISTORY_VALUES = 'showvalues';

    const HOST_AVAILABLE_FALSE = 2;

    const HOST_AVAILABLE_TRUE = 1;

    const HOST_AVAILABLE_UNKNOWN = 0;

    const HOST_ENCRYPTION_CERTIFICATE = 4;

    const HOST_ENCRYPTION_NONE = 1;

    const HOST_ENCRYPTION_PSK = 2;

    const HOST_INVENTORY_AUTOMATIC = 1;

    const HOST_INVENTORY_DISABLED = -1;

    const HOST_INVENTORY_MANUAL = 0;

    const HOST_MAINTENANCE_STATUS_OFF = 0;

    const HOST_MAINTENANCE_STATUS_ON = 1;

    const HOST_STATUS_MONITORED = 0;

    const HOST_STATUS_NOT_MONITORED = 1;

    const HOST_STATUS_PROXY_ACTIVE = 5;

    const HOST_STATUS_PROXY_PASSIVE = 6;

    const HOST_STATUS_TEMPLATE = 3;

    const HTTPSTEP_ITEM_TYPE_IN = 2;

    const HTTPSTEP_ITEM_TYPE_LASTERROR = 4;

    const HTTPSTEP_ITEM_TYPE_LASTSTEP = 3;

    const HTTPSTEP_ITEM_TYPE_RSPCODE = 0;

    const HTTPSTEP_ITEM_TYPE_TIME = 1;

    const HTTPTEST_AUTH_BASIC = 1;

    const HTTPTEST_AUTH_NONE = 0;

    const HTTPTEST_AUTH_NTLM = 2;

    const HTTPTEST_STATUS_ACTIVE = 0;

    const HTTPTEST_STATUS_DISABLED = 1;

    const HTTPTEST_STEP_FOLLOW_REDIRECTS_OFF = 0;

    const HTTPTEST_STEP_FOLLOW_REDIRECTS_ON = 1;

    const HTTPTEST_STEP_RETRIEVE_MODE_CONTENT = 0;

    const HTTPTEST_STEP_RETRIEVE_MODE_HEADERS = 1;

    const HTTPTEST_VERIFY_HOST_OFF = 0;

    const HTTPTEST_VERIFY_HOST_ON = 1;

    const HTTPTEST_VERIFY_PEER_OFF = 0;

    const HTTPTEST_VERIFY_PEER_ON = 1;

    const IMAGE_FORMAT_JPEG = 'JPEG';

    const IMAGE_FORMAT_PNG = 'PNG';

    const IMAGE_FORMAT_TEXT = 'JPEG';

    const IMAGE_TYPE_BACKGROUND = 2;

    const IMAGE_TYPE_ICON = 1;

    const IM_ESTABLISHED = 1;

    const IM_FORCED = 0;

    const IM_TREE = 2;

    const INTERFACE_PRIMARY = 1;

    const INTERFACE_SECONDARY = 0;

    const INTERFACE_TYPE_AGENT = 1;

    const INTERFACE_TYPE_ANY = -1;

    const INTERFACE_TYPE_IPMI = 3;

    const INTERFACE_TYPE_JMX = 4;

    const INTERFACE_TYPE_SNMP = 2;

    const INTERFACE_TYPE_UNKNOWN = 0;

    const INTERFACE_USE_DNS = 0;

    const INTERFACE_USE_IP = 1;

    const IPMI_AUTHTYPE_DEFAULT = -1;

    const IPMI_AUTHTYPE_MD2 = 1;

    const IPMI_AUTHTYPE_MD5 = 2;

    const IPMI_AUTHTYPE_NONE = 0;

    const IPMI_AUTHTYPE_OEM = 5;

    const IPMI_AUTHTYPE_RMCP_PLUS = 6;

    const IPMI_AUTHTYPE_STRAIGHT = 4;

    const IPMI_PRIVILEGE_ADMIN = 4;

    const IPMI_PRIVILEGE_CALLBACK = 1;

    const IPMI_PRIVILEGE_OEM = 5;

    const IPMI_PRIVILEGE_OPERATOR = 3;

    const IPMI_PRIVILEGE_USER = 2;

    const ITEM_AUTHPROTOCOL_MD5 = 0;

    const ITEM_AUTHPROTOCOL_SHA = 1;

    const ITEM_AUTHTYPE_PASSWORD = 0;

    const ITEM_AUTHTYPE_PUBLICKEY = 1;

    const ITEM_CONVERT_NO_UNITS = 1;

    const ITEM_CONVERT_WITH_UNITS = 0;

    const ITEM_DATA_TYPE_BOOLEAN = 3;

    const ITEM_DATA_TYPE_DECIMAL = 0;

    const ITEM_DATA_TYPE_HEXADECIMAL = 2;

    const ITEM_DATA_TYPE_OCTAL = 1;

    const ITEM_DELAY_FLEX_TYPE_FLEXIBLE = 0;

    const ITEM_DELAY_FLEX_TYPE_SCHEDULING = 1;

    const ITEM_LOGTYPE_CRITICAL = 9;

    const ITEM_LOGTYPE_ERROR = 4;

    const ITEM_LOGTYPE_FAILURE_AUDIT = 7;

    const ITEM_LOGTYPE_INFORMATION = 1;

    const ITEM_LOGTYPE_SUCCESS_AUDIT = 8;

    const ITEM_LOGTYPE_VERBOSE = 10;

    const ITEM_LOGTYPE_WARNING = 2;

    const ITEM_PRIVPROTOCOL_AES = 1;

    const ITEM_PRIVPROTOCOL_DES = 0;

    const ITEM_SNMPV3_SECURITYLEVEL_AUTHNOPRIV = 1;

    const ITEM_SNMPV3_SECURITYLEVEL_AUTHPRIV = 2;

    const ITEM_SNMPV3_SECURITYLEVEL_NOAUTHNOPRIV = 0;

    const ITEM_STATE_NORMAL = 0;

    const ITEM_STATE_NOTSUPPORTED = 1;

    const ITEM_STATUS_ACTIVE = 0;

    const ITEM_STATUS_DISABLED = 1;

    const ITEM_STATUS_NOTSUPPORTED = 3;

    const ITEM_TYPE_AGGREGATE = 8;

    const ITEM_TYPE_CALCULATED = 15;

    const ITEM_TYPE_DB_MONITOR = 11;

    const ITEM_TYPE_EXTERNAL = 10;

    const ITEM_TYPE_HTTPTEST = 9;

    const ITEM_TYPE_INTERNAL = 5;

    const ITEM_TYPE_IPMI = 12;

    const ITEM_TYPE_JMX = 16;

    const ITEM_TYPE_SIMPLE = 3;

    const ITEM_TYPE_SNMPTRAP = 17;

    const ITEM_TYPE_SNMPV1 = 1;

    const ITEM_TYPE_SNMPV2C = 4;

    const ITEM_TYPE_SNMPV3 = 6;

    const ITEM_TYPE_SSH = 13;

    const ITEM_TYPE_TELNET = 14;

    const ITEM_TYPE_TRAPPER = 2;

    const ITEM_TYPE_ZABBIX = 0;

    const ITEM_TYPE_ZABBIX_ACTIVE = 7;

    const ITEM_VALUE_TYPE_FLOAT = 0;

    const ITEM_VALUE_TYPE_LOG = 2;

    const ITEM_VALUE_TYPE_STR = 1;

    const ITEM_VALUE_TYPE_TEXT = 4;

    const ITEM_VALUE_TYPE_UINT64 = 3;

    const LIBXML_IMPORT_FLAGS = LIBXML_NONET;

    const LINE_TYPE_BOLD = 1;

    const LINE_TYPE_NORMAL = 0;

    const MACRO_TYPE_BOTH = 0x03;

    const MACRO_TYPE_HOSTMACRO = 0x02;

    const MACRO_TYPE_INHERITED = 0x01;

    const MAINTENANCE_STATUS_ACTIVE = 0;

    const MAINTENANCE_STATUS_APPROACH = 1;

    const MAINTENANCE_STATUS_EXPIRED = 2;

    const MAINTENANCE_TYPE_NODATA = 1;

    const MAINTENANCE_TYPE_NORMAL = 0;

    const MAP_DEFAULT_ICON = 'Server_(96)';

    const MAP_LABEL_LOC_BOTTOM = 0;

    const MAP_LABEL_LOC_DEFAULT = -1;

    const MAP_LABEL_LOC_LEFT = 1;

    const MAP_LABEL_LOC_RIGHT = 2;

    const MAP_LABEL_LOC_TOP = 3;

    const MAP_LABEL_TYPE_CUSTOM = 5;

    const MAP_LABEL_TYPE_IP = 1;

    const MAP_LABEL_TYPE_LABEL = 0;

    const MAP_LABEL_TYPE_NAME = 2;

    const MAP_LABEL_TYPE_NOTHING = 4;

    const MAP_LABEL_TYPE_STATUS = 3;

    const MAP_LINK_DRAWTYPE_BOLD_LINE = 2;

    const MAP_LINK_DRAWTYPE_DASHED_LINE = 4;

    const MAP_LINK_DRAWTYPE_DOT = 3;

    const MAP_LINK_DRAWTYPE_LINE = 0;

    const MARK_COLOR_BLUE = 3;

    const MARK_COLOR_GREEN = 2;

    const MARK_COLOR_RED = 1;

    const MEDIA_STATUS_ACTIVE = 0;

    const MEDIA_STATUS_DISABLED = 1;

    const MEDIA_TYPE_EMAIL = 0;

    const MEDIA_TYPE_EXEC = 1;

    const MEDIA_TYPE_EZ_TEXTING = 100;

    const MEDIA_TYPE_JABBER = 3;

    const MEDIA_TYPE_SMS = 2;

    const MEDIA_TYPE_STATUS_ACTIVE = 0;

    const MEDIA_TYPE_STATUS_DISABLED = 1;

    const NAME_DELIMITER = ': ';

    const NOT_EMPTY = "({}!='')&&";

    const NOT_ZERO = "({}!=0)&&";

    const OPERATION_TYPE_COMMAND = 1;

    const OPERATION_TYPE_GROUP_ADD = 4;

    const OPERATION_TYPE_GROUP_REMOVE = 5;

    const OPERATION_TYPE_HOST_ADD = 2;

    const OPERATION_TYPE_HOST_DISABLE = 9;

    const OPERATION_TYPE_HOST_ENABLE = 8;

    const OPERATION_TYPE_HOST_INVENTORY = 10;

    const OPERATION_TYPE_HOST_REMOVE = 3;

    const OPERATION_TYPE_MESSAGE = 0;

    const OPERATION_TYPE_TEMPLATE_ADD = 6;

    const OPERATION_TYPE_TEMPLATE_REMOVE = 7;

    const O_MAND = 0;

    const O_NO = 2;

    const O_OPT = 1;

    const PAGE_TYPE_CSS = 4;

    const PAGE_TYPE_CSV = 10;

    const PAGE_TYPE_HTML = 0;

    const PAGE_TYPE_HTML_BLOCK = 5;

    const PAGE_TYPE_IMAGE = 1;

    const PAGE_TYPE_JS = 3;

    const PAGE_TYPE_JSON = 6;

    const PAGE_TYPE_JSON_RPC = 7;

    const PAGE_TYPE_TEXT = 9;

    const PAGE_TYPE_TEXT_FILE = 8;

    const PAGE_TYPE_TEXT_RETURN_JSON = 11;

    const PAGE_TYPE_XML = 2;

    const PARAM_TYPE_COUNTS = 1;

    const PARAM_TYPE_TIME = 0;

    const PERM_DENY = 0;

    const PERM_READ = 2;

    const PERM_READ_WRITE = 3;

    const PRIVATE_SHARING = 1;

    const PROFILE_TYPE_ID = 1;

    const PROFILE_TYPE_INT = 2;

    const PROFILE_TYPE_STR = 3;

    const PSK_MIN_LEN = 32;

    const PUBLIC_SHARING = 0;

    const P_ACT = 16;

    const P_NO_TRIM = 64;

    const P_NZERO = 32;

    const P_SYS = 1;

    const P_UNSET_EMPTY = 2;

    const QUEUE_DETAILS = 2;

    const QUEUE_DETAIL_ITEM_COUNT = 500;

    const QUEUE_OVERVIEW = 0;

    const QUEUE_OVERVIEW_BY_PROXY = 1;

    const REPORT_PERIOD_CURRENT_MONTH = 3;

    const REPORT_PERIOD_CURRENT_WEEK = 2;

    const REPORT_PERIOD_CURRENT_YEAR = 4;

    const REPORT_PERIOD_LAST_MONTH = 6;

    const REPORT_PERIOD_LAST_WEEK = 5;

    const REPORT_PERIOD_LAST_YEAR = 7;

    const REPORT_PERIOD_TODAY = 0;

    const REPORT_PERIOD_YESTERDAY = 1;

    const SCREEN_DYNAMIC_ITEM = 1;

    const SCREEN_MODE_EDIT = 1;

    const SCREEN_MODE_JS = 3;

    const SCREEN_MODE_PREVIEW = 0;

    const SCREEN_MODE_SLIDESHOW = 2;

    const SCREEN_REFRESH_RESPONSIVENESS = 10;

    const SCREEN_REFRESH_TIMEOUT = 30;

    const SCREEN_RESOURCE_ACTIONS = 12;

    const SCREEN_RESOURCE_CHART = 18;

    const SCREEN_RESOURCE_CLOCK = 7;

    const SCREEN_RESOURCE_DATA_OVERVIEW = 10;

    const SCREEN_RESOURCE_EVENTS = 13;

    const SCREEN_RESOURCE_GRAPH = 0;

    const SCREEN_RESOURCE_HISTORY = 17;

    const SCREEN_RESOURCE_HOSTGROUP_TRIGGERS = 14;

    const SCREEN_RESOURCE_HOSTS_INFO = 4;

    const SCREEN_RESOURCE_HOST_TRIGGERS = 16;

    const SCREEN_RESOURCE_HTTPTEST_DETAILS = 21;

    const SCREEN_RESOURCE_LLD_GRAPH = 20;

    const SCREEN_RESOURCE_LLD_SIMPLE_GRAPH = 19;

    const SCREEN_RESOURCE_MAP = 2;

    const SCREEN_RESOURCE_PLAIN_TEXT = 3;

    const SCREEN_RESOURCE_SCREEN = 8;

    const SCREEN_RESOURCE_SERVER_INFO = 6;

    const SCREEN_RESOURCE_SIMPLE_GRAPH = 1;

    const SCREEN_RESOURCE_SYSTEM_STATUS = 15;

    const SCREEN_RESOURCE_TRIGGERS_INFO = 5;

    const SCREEN_RESOURCE_TRIGGERS_OVERVIEW = 9;

    const SCREEN_RESOURCE_URL = 11;

    const SCREEN_SIMPLE_ITEM = 0;

    const SCREEN_SORT_TRIGGERS_DATE_DESC = 0;

    const SCREEN_SORT_TRIGGERS_HOST_NAME_ASC = 2;

    const SCREEN_SORT_TRIGGERS_RECIPIENT_ASC = 11;

    const SCREEN_SORT_TRIGGERS_RECIPIENT_DESC = 12;

    const SCREEN_SORT_TRIGGERS_SEVERITY_DESC = 1;

    const SCREEN_SORT_TRIGGERS_STATUS_ASC = 7;

    const SCREEN_SORT_TRIGGERS_STATUS_DESC = 8;

    const SCREEN_SORT_TRIGGERS_TIME_ASC = 3;

    const SCREEN_SORT_TRIGGERS_TIME_DESC = 4;

    const SCREEN_SORT_TRIGGERS_TYPE_ASC = 5;

    const SCREEN_SORT_TRIGGERS_TYPE_DESC = 6;

    const SCREEN_SURROGATE_MAX_COLUMNS_DEFAULT = 3;

    const SCREEN_SURROGATE_MAX_COLUMNS_MAX = 100;

    const SCREEN_SURROGATE_MAX_COLUMNS_MIN = 1;

    const SEC_PER_DAY = 86400;

    const SEC_PER_HOUR = 3600;

    const SEC_PER_MIN = 60;

    const SEC_PER_MONTH = 2592000;

    const SEC_PER_WEEK = 604800;

    const SEC_PER_YEAR = 31536000;

    const SERVER_CHECK_INTERVAL = 10;

    const SERVICE_ALGORITHM_MAX = 1;

    const SERVICE_ALGORITHM_MIN = 2;

    const SERVICE_ALGORITHM_NONE = 0;

    const SERVICE_SHOW_SLA_OFF = 0;

    const SERVICE_SHOW_SLA_ON = 1;

    const SERVICE_SLA = 99.05;

    const SERVICE_STATUS_OK = 0;

    const SERVICE_TIME_TYPE_DOWNTIME = 1;

    const SERVICE_TIME_TYPE_ONETIME_DOWNTIME = 2;

    const SERVICE_TIME_TYPE_UPTIME = 0;

    const SMTP_AUTHENTICATION_NONE = 0;

    const SMTP_AUTHENTICATION_NORMAL = 1;

    const SMTP_CONNECTION_SECURITY_NONE = 0;

    const SMTP_CONNECTION_SECURITY_SSL_TLS = 2;

    const SMTP_CONNECTION_SECURITY_STARTTLS = 1;

    const SNMP_BULK_DISABLED = 0;

    const SNMP_BULK_ENABLED = 1;

    const SPACE = '&nbsp;';

    const STYLE_HORIZONTAL = 0;

    const STYLE_LEFT = 0;

    const STYLE_TOP = 1;

    const STYLE_VERTICAL = 1;

    const SVC_AGENT = 9;

    const SVC_FTP = 3;

    const SVC_HTTP = 4;

    const SVC_HTTPS = 14;

    const SVC_ICMPPING = 12;

    const SVC_IMAP = 7;

    const SVC_LDAP = 1;

    const SVC_NNTP = 6;

    const SVC_POP = 5;

    const SVC_SMTP = 2;

    const SVC_SNMPv1 = 10;

    const SVC_SNMPv2c = 11;

    const SVC_SNMPv3 = 13;

    const SVC_SSH = 0;

    const SVC_TCP = 8;

    const SVC_TELNET = 15;

    const SYSMAP_ELEMENT_AREA_TYPE_CUSTOM = 1;

    const SYSMAP_ELEMENT_AREA_TYPE_FIT = 0;

    const SYSMAP_ELEMENT_AREA_VIEWTYPE_GRID = 0;

    const SYSMAP_ELEMENT_ICON_DISABLED = 4;

    const SYSMAP_ELEMENT_ICON_MAINTENANCE = 3;

    const SYSMAP_ELEMENT_ICON_OFF = 1;

    const SYSMAP_ELEMENT_ICON_ON = 0;

    const SYSMAP_ELEMENT_SUBTYPE_HOST_GROUP = 0;

    const SYSMAP_ELEMENT_SUBTYPE_HOST_GROUP_ELEMENTS = 1;

    const SYSMAP_ELEMENT_TYPE_HOST = 0;

    const SYSMAP_ELEMENT_TYPE_HOST_GROUP = 3;

    const SYSMAP_ELEMENT_TYPE_IMAGE = 4;

    const SYSMAP_ELEMENT_TYPE_MAP = 1;

    const SYSMAP_ELEMENT_TYPE_TRIGGER = 2;

    const SYSMAP_ELEMENT_USE_ICONMAP_OFF = 0;

    const SYSMAP_ELEMENT_USE_ICONMAP_ON = 1;

    const SYSMAP_EXPAND_MACROS_OFF = 0;

    const SYSMAP_EXPAND_MACROS_ON = 1;

    const SYSMAP_GRID_ALIGN_OFF = 0;

    const SYSMAP_GRID_ALIGN_ON = 1;

    const SYSMAP_GRID_SHOW_OFF = 0;

    const SYSMAP_GRID_SHOW_ON = 1;

    const SYSMAP_HIGHLIGHT_OFF = 0;

    const SYSMAP_HIGHLIGHT_ON = 1;

    const SYSMAP_LABEL_ADVANCED_OFF = 0;

    const SYSMAP_LABEL_ADVANCED_ON = 1;

    const THEME_DEFAULT = 'default';

    const TIMEPERIOD_TYPE_DAILY = 2;

    const TIMEPERIOD_TYPE_HOURLY = 1;

    const TIMEPERIOD_TYPE_MONTHLY = 4;

    const TIMEPERIOD_TYPE_ONETIME = 0;

    const TIMEPERIOD_TYPE_WEEKLY = 3;

    const TIMEPERIOD_TYPE_YEARLY = 5;

    const TIMESTAMP_FORMAT = 'YmdHis';

    const TIMESTAMP_FORMAT_ZERO_TIME = 'Ymd0000';

    const TIME_TYPE_HOST = 2;

    const TIME_TYPE_LOCAL = 0;

    const TIME_TYPE_SERVER = 1;

    const TRIGGERS_OPTION_ALL = 2;

    const TRIGGERS_OPTION_IN_PROBLEM = 3;

    const TRIGGERS_OPTION_RECENT_PROBLEM = 1;

    const TRIGGER_MULT_EVENT_DISABLED = 0;

    const TRIGGER_MULT_EVENT_ENABLED = 1;

    const TRIGGER_SEVERITY_AVERAGE = 3;

    const TRIGGER_SEVERITY_COUNT = 6;

    const TRIGGER_SEVERITY_DISASTER = 5;

    const TRIGGER_SEVERITY_HIGH = 4;

    const TRIGGER_SEVERITY_INFORMATION = 1;

    const TRIGGER_SEVERITY_NOT_CLASSIFIED = 0;

    const TRIGGER_SEVERITY_WARNING = 2;

    const TRIGGER_STATE_NORMAL = 0;

    const TRIGGER_STATE_UNKNOWN = 1;

    const TRIGGER_STATUS_DISABLED = 1;

    const TRIGGER_STATUS_ENABLED = 0;

    const TRIGGER_VALUE_FALSE = 0;

    const TRIGGER_VALUE_TRUE = 1;

    const T_ZBX_CLR = 5;

    const T_ZBX_DBL = 2;

    const T_ZBX_DBL_BIG = 9;

    const T_ZBX_DBL_STR = 10;

    const T_ZBX_INT = 1;

    const T_ZBX_STR = 0;

    const T_ZBX_TP = 11;

    const UNKNOWN_VALUE = '';

    const USER_TYPE_SUPER_ADMIN = 3;

    const USER_TYPE_ZABBIX_ADMIN = 2;

    const USER_TYPE_ZABBIX_USER = 1;

    const VALIGN_BOTTOM = 2;

    const VALIGN_DEFAULT = 0;

    const VALIGN_MIDDLE = 0;

    const VALIGN_TOP = 1;

    const WIDGET_DISCOVERY_STATUS = 'dscvry';

    const WIDGET_FAVOURITE_GRAPHS = 'favgrph';

    const WIDGET_FAVOURITE_MAPS = 'favmap';

    const WIDGET_FAVOURITE_SCREENS = 'favscr';

    const WIDGET_HAT_EVENTACK = 'hat_eventack';

    const WIDGET_HAT_EVENTACTIONMCMDS = 'hat_eventactionmcmds';

    const WIDGET_HAT_EVENTACTIONMSGS = 'hat_eventactionmsgs';

    const WIDGET_HAT_EVENTDETAILS = 'hat_eventdetails';

    const WIDGET_HAT_EVENTLIST = 'hat_eventlist';

    const WIDGET_HAT_TRIGGERDETAILS = 'hat_triggerdetails';

    const WIDGET_HOST_STATUS = 'hoststat';

    const WIDGET_LAST_ISSUES = 'lastiss';

    const WIDGET_SEARCH_HOSTGROUP = 'search_hostgroup';

    const WIDGET_SEARCH_HOSTS = 'search_hosts';

    const WIDGET_SEARCH_TEMPLATES = 'search_templates';

    const WIDGET_SLIDESHOW = 'hat_slides';

    const WIDGET_SYSTEM_STATUS = 'syssum';

    const WIDGET_WEB_OVERVIEW = 'webovr';

    const WIDGET_ZABBIX_STATUS = 'stszbx';

    const XML_ARRAY = 0x02;

    const XML_INDEXED_ARRAY = 0x04;

    const XML_REQUIRED = 0x08;

    const XML_STRING = 0x01;

    const XML_TAG_DEPENDENCY = 'dependency';

    const XML_TAG_GRAPH = 'graph';

    const XML_TAG_GRAPH_ELEMENT = 'graph_element';

    const XML_TAG_HOST = 'host';

    const XML_TAG_HOSTINVENTORY = 'host_inventory';

    const XML_TAG_ITEM = 'item';

    const XML_TAG_MACRO = 'macro';

    const XML_TAG_TRIGGER = 'trigger';

    const ZABBIX_API_VERSION = '3.0.0';

    const ZABBIX_COPYRIGHT_FROM = '2001';

    const ZABBIX_COPYRIGHT_TO = '2016';

    const ZABBIX_DB_VERSION = 3000000;

    const ZABBIX_EXPORT_VERSION = '3.0';

    const ZABBIX_HOMEPAGE = 'http://www.zabbix.com';

    const ZABBIX_VERSION = '3.0.0';

    const ZBX_ACKNOWLEDGE_ALL = 2;

    const ZBX_ACKNOWLEDGE_PROBLEM = 1;

    const ZBX_ACKNOWLEDGE_SELECTED = 0;

    const ZBX_ACK_STS_ANY = 1;

    const ZBX_ACK_STS_WITH_LAST_UNACK = 3;

    const ZBX_ACK_STS_WITH_UNACK = 2;

    const ZBX_AGENT_OTHER = -1;

    const ZBX_API_ERROR_INTERNAL = 111;

    const ZBX_API_ERROR_NO_AUTH = 200;

    const ZBX_API_ERROR_NO_METHOD = 300;

    const ZBX_API_ERROR_PARAMETERS = 100;

    const ZBX_API_ERROR_PERMISSIONS = 120;

    const ZBX_AUTH_HTTP = 2;

    const ZBX_AUTH_INTERNAL = 0;

    const ZBX_AUTH_LDAP = 1;

    const ZBX_BYTE_SUFFIXES = 'KMGT';

    const ZBX_DB_DB2 = 'IBM_DB2';

    const ZBX_DB_MAX_ID = '9223372036854775807';

    const ZBX_DB_MAX_INSERTS = 10000;

    const ZBX_DB_MYSQL = 'MYSQL';

    const ZBX_DB_ORACLE = 'ORACLE';

    const ZBX_DB_POSTGRESQL = 'POSTGRESQL';

    const ZBX_DB_SQLITE3 = 'SQLITE3';

    const ZBX_DEFAULT_AGENT = 'Zabbix';

    const ZBX_DEFAULT_IMPORT_HOST_GROUP = 'Imported hosts';

    const ZBX_DEFAULT_INTERVAL = '1-7,00:00-24:00';

    const ZBX_DEFAULT_KEY_DB_MONITOR = 'db.odbc.select[<unique short description>,<dsn>]';

    const ZBX_DEFAULT_KEY_DB_MONITOR_DISCOVERY = 'db.odbc.discovery[<unique short description>,<dsn>]';

    const ZBX_DEFAULT_KEY_JMX = 'jmx[<object name>,<attribute name>]';

    const ZBX_DEFAULT_KEY_SSH = 'ssh.run[<unique short description>,<ip>,<port>,<encoding>]';

    const ZBX_DEFAULT_KEY_TELNET = 'telnet.run[<unique short description>,<ip>,<port>,<encoding>]';

    const ZBX_DEFAULT_THEME = 'blue-theme';

    const ZBX_DEFAULT_URL = 'zabbix.php?action=dashboard.view';

    const ZBX_DISCOVERER_IPRANGE_LIMIT = 65536;

    const ZBX_DROPDOWN_FIRST_ALL = 1;

    const ZBX_DROPDOWN_FIRST_NONE = 0;

    const ZBX_FLAG_DISCOVERY_CREATED = 0x4;

    const ZBX_FLAG_DISCOVERY_NORMAL = 0x0;

    const ZBX_FLAG_DISCOVERY_PROTOTYPE = 0x2;

    const ZBX_FLAG_DISCOVERY_RULE = 0x1;

    const ZBX_FONT_NAME = 'DejaVuSans';

    const ZBX_GRAPH_FONT_NAME = 'DejaVuSans';

    const ZBX_GRAPH_LEGEND_HEIGHT = 120;

    const ZBX_GRAPH_MAX_SKIP_CELL = 16;

    const ZBX_GRAPH_MAX_SKIP_DELAY = 4;

    const ZBX_GUEST_USER = 'guest';

    const ZBX_HAVE_IPV6 = 1;

    const ZBX_HISTORY_PERIOD = 86400;

    const ZBX_HOST_INTERFACE_WIDTH = 750;

    const ZBX_ICON_PREVIEW_HEIGHT = 24;

    const ZBX_ICON_PREVIEW_WIDTH = 24;

    const ZBX_INTERNAL_GROUP = 1;

    const ZBX_ITEM_DELAY_DEFAULT = 30;

    const ZBX_JAN_2038 = 2145916800;

    const ZBX_LOGIN_ATTEMPTS = 5;

    const ZBX_LOGIN_BLOCK = 30;

    const ZBX_MAX_DATE = 2147483647;

    const ZBX_MAX_IMAGE_SIZE = 1048576;

    const ZBX_MAX_PERIOD = 63072000;

    const ZBX_MAX_PORT_NUMBER = 65535;

    const ZBX_MAX_TREND_DIFF = 3600;

    const ZBX_MIN_PERIOD = 60;

    const ZBX_MIN_PORT_NUMBER = 0;

    const ZBX_NOT_INTERNAL_GROUP = 0;

    const ZBX_OVERVIEW_HELP_MIN_WIDTH = 125;

    const ZBX_PERIOD_DEFAULT = 3600;

    const ZBX_PRECISION_10 = 10;

    const ZBX_PREG_DEF_FONT_STRING = '/^[0-9\.:% ]+$/';

    const ZBX_PREG_DNS_FORMAT = '([0-9a-zA-Z_\.\-$]|\{\$?'.self::ZBX_PREG_MACRO_NAME.'\})*';

    const ZBX_PREG_EXPRESSION_LLD_MACROS = '(\{\#'.self::ZBX_PREG_MACRO_NAME_LLD.'\})';

    const ZBX_PREG_HOST_FORMAT = self::ZBX_PREG_INTERNAL_NAMES;

    const ZBX_PREG_INTERNAL_NAMES = '([0-9a-zA-Z_\. \-]+)';

    const ZBX_PREG_MACRO_NAME = '([A-Z0-9\._]+)';

    const ZBX_PREG_MACRO_NAME_FORMAT = '(\{[A-Z\.]+\})';

    const ZBX_PREG_MACRO_NAME_LLD = '([A-Z0-9\._]+)';

    const ZBX_PREG_NUMBER = '([\-+]?[0-9]+[.]?[0-9]*['.self::ZBX_BYTE_SUFFIXES.self::ZBX_TIME_SUFFIXES.']?)';

    const ZBX_PREG_PARAMS = '(['.self::ZBX_PREG_PRINT.']+?)?';

    const ZBX_PREG_PRINT = '^\x{00}-\x{1F}';

    const ZBX_SCRIPT_EXECUTE_ON_AGENT = 0;

    const ZBX_SCRIPT_EXECUTE_ON_SERVER = 1;

    const ZBX_SCRIPT_TIMEOUT = 60;

    const ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT = 0;

    const ZBX_SCRIPT_TYPE_GLOBAL_SCRIPT = 4;

    const ZBX_SCRIPT_TYPE_IPMI = 1;

    const ZBX_SCRIPT_TYPE_SSH = 2;

    const ZBX_SCRIPT_TYPE_TELNET = 3;

    const ZBX_SESSION_ACTIVE = 0;

    const ZBX_SESSION_PASSIVE = 1;

    const ZBX_SOCKET_BYTES_LIMIT = 1048576;

    const ZBX_SOCKET_TIMEOUT = 3;

    const ZBX_SORT_DOWN = 'DESC';

    const ZBX_SORT_UP = 'ASC';

    const ZBX_TEXTAREA_2DIGITS_WIDTH = 35;

    const ZBX_TEXTAREA_4DIGITS_WIDTH = 50;

    const ZBX_TEXTAREA_BIG_WIDTH = 524;

    const ZBX_TEXTAREA_COLOR_WIDTH = 96;

    const ZBX_TEXTAREA_FILTER_BIG_WIDTH = 524;

    const ZBX_TEXTAREA_FILTER_SMALL_WIDTH = 150;

    const ZBX_TEXTAREA_FILTER_STANDARD_WIDTH = 300;

    const ZBX_TEXTAREA_INTERFACE_DNS_WIDTH = 175;

    const ZBX_TEXTAREA_INTERFACE_IP_WIDTH = 225;

    const ZBX_TEXTAREA_INTERFACE_PORT_WIDTH = 100;

    const ZBX_TEXTAREA_INTERFACE_USEIP_WIDTH = 100;

    const ZBX_TEXTAREA_MACRO_VALUE_WIDTH = 250;

    const ZBX_TEXTAREA_MACRO_WIDTH = 200;

    const ZBX_TEXTAREA_NUMERIC_BIG_WIDTH = 150;

    const ZBX_TEXTAREA_NUMERIC_STANDARD_WIDTH = 75;

    const ZBX_TEXTAREA_SMALL_WIDTH = 150;

    const ZBX_TEXTAREA_STANDARD_ROWS = 7;

    const ZBX_TEXTAREA_STANDARD_WIDTH = 300;

    const ZBX_TEXTAREA_TINY_WIDTH = 75;

    const ZBX_TIME_SUFFIXES = 'smhdw';

    const ZBX_UNITS_ROUNDOFF_LOWER_LIMIT = 6;

    const ZBX_UNITS_ROUNDOFF_MIDDLE_LIMIT = 4;

    const ZBX_UNITS_ROUNDOFF_THRESHOLD = 0.01;

    const ZBX_UNITS_ROUNDOFF_UPPER_LIMIT = 2;

    const ZBX_USER_ONLINE_TIME = 600;

    const ZBX_VALID_ERROR = 1;

    const ZBX_VALID_OK = 0;

    const ZBX_VALID_WARNING = 2;

    const ZBX_WIDGET_ROWS = 20;

    /**
     * Returns the API url for all requests.
     *
     * @return string API url
     */
    public function getApiUrl();

    /**
     * Sets the API url for all requests.
     *
     * @param string $apiUrl API url
     *
     * @return ZabbixApiInterface
     */
    public function setApiUrl($apiUrl);

    /**
     * Sets the API authorization ID.
     *
     * @param string $authToken API auth ID
     *
     * @return ZabbixApiInterface
     */
    public function setAuthToken($authToken);

    /**
     * Sets the username and password for the HTTP basic authorization.
     *
     * @param string $user HTTP basic authorization username
     * @param string $password HTTP basic authorization password
     *
     * @return ZabbixApiInterface
     */
    public function setBasicAuthorization($user, $password);

    /**
     * Returns the default params.
     *
     * @return array Array with default params
     */
    public function getDefaultParams();

    /**
     * Sets the default params.
     *
     * @param array $defaultParams Array with default params
     *
     * @throws Exception
     *
     * @return ZabbixApiInterface
     */
    public function setDefaultParams(array $defaultParams);

    /**
     * Sets the flag to print communication requests/responses.
     *
     * @param bool $print Boolean if requests/responses should be printed out
     *
     * @return ZabbixApiInterface
     */
    public function printCommunication($print = true);

    /**
     * Returns the last JSON API response.
     *
     * @return ResponseInterface
     */
    public function getResponse();

    /**
     * Logout from the API.
     *
     * This will also reset the auth Token.
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param array $params Parameters to pass through
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function userLogout($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "action.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function actionCreate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "action.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function actionDelete($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "action.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function actionGet($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "action.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function actionPk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "action.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function actionPkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "action.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function actionTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "action.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function actionUpdate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "action.validateoperationconditions".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function actionValidateOperationConditions($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "action.validateoperationsintegrity".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function actionValidateOperationsIntegrity($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "alert.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function alertGet($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "alert.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function alertPk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "alert.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function alertPkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "alert.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function alertTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "api.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function apiPk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "api.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function apiPkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "api.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function apiTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "apiinfo.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function apiinfoPk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "apiinfo.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function apiinfoPkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "apiinfo.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function apiinfoTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "apiinfo.version".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function apiinfoVersion($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "application.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function applicationCreate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "application.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function applicationDelete($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "application.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function applicationGet($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "application.massadd".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function applicationMassAdd($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "application.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function applicationPk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "application.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function applicationPkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "application.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function applicationTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "application.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function applicationUpdate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "configuration.export".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function configurationExport($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "configuration.import".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function configurationImport($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "configuration.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function configurationPk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "configuration.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function configurationPkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "configuration.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function configurationTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "dcheck.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function dcheckGet($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "dcheck.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function dcheckPk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "dcheck.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function dcheckPkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "dcheck.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function dcheckTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "dhost.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function dhostGet($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "dhost.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function dhostPk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "dhost.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function dhostPkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "dhost.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function dhostTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "discoveryrule.copy".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function discoveryruleCopy($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "discoveryrule.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function discoveryruleCreate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "discoveryrule.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function discoveryruleDelete($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "discoveryrule.findinterfaceforitem".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function discoveryruleFindInterfaceForItem($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "discoveryrule.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function discoveryruleGet($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "discoveryrule.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function discoveryrulePk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "discoveryrule.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function discoveryrulePkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "discoveryrule.synctemplates".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function discoveryruleSyncTemplates($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "discoveryrule.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function discoveryruleTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "discoveryrule.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function discoveryruleUpdate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "drule.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function druleCreate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "drule.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function druleDelete($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "drule.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function druleGet($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "drule.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function drulePk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "drule.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function drulePkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "drule.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function druleTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "drule.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function druleUpdate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "dservice.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function dserviceGet($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "dservice.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function dservicePk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "dservice.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function dservicePkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "dservice.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function dserviceTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "event.acknowledge".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function eventAcknowledge($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "event.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function eventGet($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "event.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function eventPk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "event.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function eventPkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "event.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function eventTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "graph.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function graphCreate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "graph.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function graphDelete($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "graph.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function graphGet($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "graph.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function graphPk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "graph.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function graphPkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "graph.synctemplates".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function graphSyncTemplates($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "graph.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function graphTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "graph.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function graphUpdate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "graphitem.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function graphitemGet($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "graphitem.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function graphitemPk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "graphitem.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function graphitemPkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "graphitem.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function graphitemTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "graphprototype.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function graphprototypeCreate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "graphprototype.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function graphprototypeDelete($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "graphprototype.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function graphprototypeGet($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "graphprototype.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function graphprototypePk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "graphprototype.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function graphprototypePkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "graphprototype.synctemplates".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function graphprototypeSyncTemplates($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "graphprototype.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function graphprototypeTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "graphprototype.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function graphprototypeUpdate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "history.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function historyGet($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "history.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function historyPk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "history.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function historyPkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "history.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function historyTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "host.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostCreate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "host.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostDelete($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "host.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostGet($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "host.massadd".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostMassAdd($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "host.massremove".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostMassRemove($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "host.massupdate".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostMassUpdate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "host.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostPk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "host.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostPkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "host.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "host.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostUpdate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "hostgroup.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostgroupCreate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "hostgroup.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostgroupDelete($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "hostgroup.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostgroupGet($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "hostgroup.massadd".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostgroupMassAdd($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "hostgroup.massremove".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostgroupMassRemove($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "hostgroup.massupdate".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostgroupMassUpdate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "hostgroup.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostgroupPk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "hostgroup.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostgroupPkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "hostgroup.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostgroupTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "hostgroup.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostgroupUpdate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "hostinterface.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostinterfaceCreate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "hostinterface.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostinterfaceDelete($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "hostinterface.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostinterfaceGet($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "hostinterface.massadd".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostinterfaceMassAdd($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "hostinterface.massremove".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostinterfaceMassRemove($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "hostinterface.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostinterfacePk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "hostinterface.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostinterfacePkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "hostinterface.replacehostinterfaces".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostinterfaceReplaceHostInterfaces($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "hostinterface.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostinterfaceTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "hostinterface.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostinterfaceUpdate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "hostprototype.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostprototypeCreate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "hostprototype.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostprototypeDelete($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "hostprototype.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostprototypeGet($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "hostprototype.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostprototypePk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "hostprototype.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostprototypePkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "hostprototype.synctemplates".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostprototypeSyncTemplates($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "hostprototype.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostprototypeTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "hostprototype.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function hostprototypeUpdate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "httptest.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function httptestCreate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "httptest.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function httptestDelete($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "httptest.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function httptestGet($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "httptest.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function httptestPk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "httptest.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function httptestPkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "httptest.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function httptestTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "httptest.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function httptestUpdate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "iconmap.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function iconmapCreate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "iconmap.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function iconmapDelete($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "iconmap.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function iconmapGet($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "iconmap.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function iconmapPk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "iconmap.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function iconmapPkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "iconmap.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function iconmapTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "iconmap.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function iconmapUpdate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "image.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function imageCreate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "image.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function imageDelete($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "image.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function imageGet($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "image.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function imagePk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "image.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function imagePkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "image.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function imageTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "image.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function imageUpdate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "item.addrelatedobjects".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemAddRelatedObjects($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "item.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemCreate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "item.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemDelete($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "item.findinterfaceforitem".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemFindInterfaceForItem($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "item.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemGet($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "item.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemPk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "item.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemPkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "item.synctemplates".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemSyncTemplates($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "item.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "item.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemUpdate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "item.validateinventorylinks".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemValidateInventoryLinks($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "itemprototype.addrelatedobjects".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemprototypeAddRelatedObjects($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "itemprototype.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemprototypeCreate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "itemprototype.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemprototypeDelete($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "itemprototype.findinterfaceforitem".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemprototypeFindInterfaceForItem($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "itemprototype.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemprototypeGet($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "itemprototype.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemprototypePk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "itemprototype.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemprototypePkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "itemprototype.synctemplates".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemprototypeSyncTemplates($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "itemprototype.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemprototypeTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "itemprototype.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function itemprototypeUpdate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "maintenance.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function maintenanceCreate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "maintenance.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function maintenanceDelete($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "maintenance.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function maintenanceGet($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "maintenance.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function maintenancePk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "maintenance.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function maintenancePkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "maintenance.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function maintenanceTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "maintenance.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function maintenanceUpdate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "map.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function mapCreate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "map.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function mapDelete($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "map.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function mapGet($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "map.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function mapPk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "map.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function mapPkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "map.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function mapTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "map.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function mapUpdate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "mediatype.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function mediatypeCreate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "mediatype.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function mediatypeDelete($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "mediatype.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function mediatypeGet($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "mediatype.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function mediatypePk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "mediatype.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function mediatypePkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "mediatype.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function mediatypeTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "mediatype.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function mediatypeUpdate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "proxy.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function proxyCreate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "proxy.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function proxyDelete($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "proxy.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function proxyGet($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "proxy.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function proxyPk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "proxy.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function proxyPkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "proxy.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function proxyTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "proxy.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function proxyUpdate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "screen.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function screenCreate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "screen.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function screenDelete($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "screen.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function screenGet($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "screen.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function screenPk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "screen.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function screenPkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "screen.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function screenTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "screen.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function screenUpdate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "screenitem.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function screenitemCreate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "screenitem.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function screenitemDelete($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "screenitem.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function screenitemGet($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "screenitem.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function screenitemPk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "screenitem.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function screenitemPkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "screenitem.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function screenitemTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "screenitem.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function screenitemUpdate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "screenitem.updatebyposition".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function screenitemUpdateByPosition($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "script.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function scriptCreate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "script.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function scriptDelete($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "script.execute".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function scriptExecute($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "script.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function scriptGet($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "script.getscriptsbyhosts".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function scriptGetScriptsByHosts($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "script.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function scriptPk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "script.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function scriptPkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "script.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function scriptTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "script.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function scriptUpdate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "service.adddependencies".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function serviceAddDependencies($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "service.addtimes".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function serviceAddTimes($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "service.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function serviceCreate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "service.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function serviceDelete($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "service.deletedependencies".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function serviceDeleteDependencies($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "service.deletetimes".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function serviceDeleteTimes($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "service.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function serviceGet($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "service.getsla".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function serviceGetSla($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "service.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function servicePk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "service.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function servicePkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "service.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function serviceTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "service.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function serviceUpdate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "service.validateaddtimes".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function serviceValidateAddTimes($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "service.validatedelete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function serviceValidateDelete($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "service.validateupdate".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function serviceValidateUpdate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "template.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templateCreate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "template.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templateDelete($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "template.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templateGet($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "template.massadd".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templateMassAdd($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "template.massremove".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templateMassRemove($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "template.massupdate".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templateMassUpdate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "template.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templatePk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "template.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templatePkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "template.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templateTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "template.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templateUpdate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "templatescreen.copy".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templatescreenCopy($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "templatescreen.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templatescreenCreate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "templatescreen.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templatescreenDelete($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "templatescreen.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templatescreenGet($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "templatescreen.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templatescreenPk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "templatescreen.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templatescreenPkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "templatescreen.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templatescreenTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "templatescreen.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templatescreenUpdate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "templatescreenitem.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templatescreenitemGet($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "templatescreenitem.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templatescreenitemPk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "templatescreenitem.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templatescreenitemPkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "templatescreenitem.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function templatescreenitemTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "trend.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function trendGet($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "trend.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function trendPk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "trend.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function trendPkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "trend.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function trendTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "trigger.adddependencies".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function triggerAddDependencies($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "trigger.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function triggerCreate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "trigger.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function triggerDelete($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "trigger.deletedependencies".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function triggerDeleteDependencies($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "trigger.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function triggerGet($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "trigger.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function triggerPk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "trigger.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function triggerPkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "trigger.synctemplatedependencies".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function triggerSyncTemplateDependencies($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "trigger.synctemplates".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function triggerSyncTemplates($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "trigger.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function triggerTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "trigger.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function triggerUpdate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "triggerprototype.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function triggerprototypeCreate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "triggerprototype.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function triggerprototypeDelete($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "triggerprototype.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function triggerprototypeGet($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "triggerprototype.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function triggerprototypePk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "triggerprototype.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function triggerprototypePkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "triggerprototype.synctemplatedependencies".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function triggerprototypeSyncTemplateDependencies($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "triggerprototype.synctemplates".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function triggerprototypeSyncTemplates($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "triggerprototype.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function triggerprototypeTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "triggerprototype.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function triggerprototypeUpdate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "user.addmedia".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function userAddMedia($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "user.checkauthentication".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function userCheckAuthentication($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "user.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function userCreate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "user.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function userDelete($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "user.deletemedia".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function userDeleteMedia($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "user.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function userGet($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "user.login".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function userLogin($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "user.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function userPk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "user.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function userPkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "user.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function userTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "user.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function userUpdate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "user.updatemedia".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function userUpdateMedia($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "user.updateprofile".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function userUpdateProfile($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "usergroup.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usergroupCreate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "usergroup.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usergroupDelete($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "usergroup.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usergroupGet($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "usergroup.massadd".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usergroupMassAdd($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "usergroup.massupdate".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usergroupMassUpdate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "usergroup.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usergroupPk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "usergroup.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usergroupPkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "usergroup.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usergroupTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "usergroup.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usergroupUpdate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "usermacro.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usermacroCreate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "usermacro.createglobal".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usermacroCreateGlobal($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "usermacro.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usermacroDelete($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "usermacro.deleteglobal".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usermacroDeleteGlobal($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "usermacro.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usermacroGet($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "usermacro.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usermacroPk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "usermacro.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usermacroPkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "usermacro.replacemacros".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usermacroReplaceMacros($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "usermacro.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usermacroTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "usermacro.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usermacroUpdate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "usermacro.updateglobal".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usermacroUpdateGlobal($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "usermedia.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usermediaGet($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "usermedia.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usermediaPk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "usermedia.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usermediaPkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "usermedia.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function usermediaTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "valuemap.create".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function valuemapCreate($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "valuemap.delete".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function valuemapDelete($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "valuemap.get".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function valuemapGet($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "valuemap.pk".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function valuemapPk($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "valuemap.pkoption".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function valuemapPkOption($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "valuemap.tablename".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function valuemapTableName($params = [], $arrayKeyProperty = null, $assoc = true);

    /**
     * Requests the Zabbix API and returns the response of the method "valuemap.update".
     *
     * The $params Array can be used, to pass parameters to the Zabbix API.
     * For more information about these parameters, check the Zabbix API
     * documentation at https://www.zabbix.com/documentation/.
     *
     * The $arrayKeyProperty can be used to get an associative instead of an
     * indexed array as response. A valid value for the $arrayKeyProperty is
     * is any property of the returned JSON objects (e.g. "name", "host",
     * "hostid", "graphid", "screenitemid").
     *
     * @param mixed $params Zabbix API parameters
     * @param string|null $arrayKeyProperty Object property for key of array
     * @param bool $assoc Return the value as an associative array instead of an instance of stdClass
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function valuemapUpdate($params = [], $arrayKeyProperty = null, $assoc = true);
}
